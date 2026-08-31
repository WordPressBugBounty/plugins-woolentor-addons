#!/bin/bash

# Publish a branch of this private repo to the PUBLIC GitHub repo, leaving out
# everything listed in .publicignore.
#
# Why this script exists instead of a plain `git push`:
#
#   git push cannot filter files. It pushes commit objects, not working trees,
#   so `git push public master` would publish every file that branch has ever
#   contained — including files deleted years ago. .publicignore would do
#   nothing.
#
#   So the public repo gets its own synthetic history. Each sync builds a clean
#   tree from the source branch, commits it on top of the previous public
#   commit, and pushes only that. The excluded files never exist in the public
#   history at all.
#
# Usage:
#   ./push-public.sh --setup [git-url]        # install the guard hook, add remote
#   ./push-public.sh master                   # master -> public/master
#   ./push-public.sh periodic-updates main    # source branch -> public branch
#   ./push-public.sh master --dry-run         # show what would go, push nothing
#   ./push-public.sh master -m "Release 3.4.9"
#
# The private 'origin' remote, its branches and its history are never touched.

set -e

# Always work from the repo root, no matter where the script is called from.
cd "$(dirname "$0")"
REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || true)"

if [ -z "$REPO_ROOT" ]; then
    echo "Error: not inside a git repository."
    exit 1
fi

cd "$REPO_ROOT"

PUBLIC_REMOTE="public"
IGNORE_FILE=".publicignore"
HOOK_FILE=".git/hooks/pre-push"

# The pre-push hook looks for this. Set only around our own push.
SYNC_ENV_VAR="WL_PUBLIC_SYNC"

# ---------------------------------------------------------------------------
# --setup: install the guard hook and optionally add the remote
# ---------------------------------------------------------------------------

install_hook() {
    local hook_dir
    hook_dir="$(dirname "$HOOK_FILE")"
    mkdir -p "$hook_dir"

    if [ -f "$HOOK_FILE" ] && ! grep -q "$SYNC_ENV_VAR" "$HOOK_FILE"; then
        echo "Warning: $HOOK_FILE already exists and is not ours."
        echo "         Backing it up to ${HOOK_FILE}.bak and replacing it."
        cp "$HOOK_FILE" "${HOOK_FILE}.bak"
    fi

    cat > "$HOOK_FILE" <<'HOOK'
#!/bin/bash
# Installed by push-public.sh — blocks manual pushes to the public remote.
#
# The public repo must only ever receive the filtered tree that push-public.sh
# builds. A plain `git push public <branch>` would publish the full private
# history, including every file .publicignore is meant to keep out.

remote_name="$1"

if [ "$remote_name" = "public" ] && [ "$WL_PUBLIC_SYNC" != "1" ]; then
    echo ""
    echo "  BLOCKED: direct push to the 'public' remote is not allowed."
    echo ""
    echo "  A plain git push would publish the FULL private history —"
    echo "  every file .publicignore is meant to keep out."
    echo ""
    echo "  Use:  ./push-public.sh <branch>"
    echo ""
    exit 1
fi

exit 0
HOOK

    chmod +x "$HOOK_FILE"
    echo "Installed guard hook: $HOOK_FILE"
}

if [ "$1" = "--setup" ]; then
    install_hook

    if [ -n "$2" ]; then
        if git remote get-url "$PUBLIC_REMOTE" >/dev/null 2>&1; then
            echo "Remote '$PUBLIC_REMOTE' already exists:"
            echo "  $(git remote get-url "$PUBLIC_REMOTE")"
            echo "Change it with: git remote set-url $PUBLIC_REMOTE $2"
        else
            git remote add "$PUBLIC_REMOTE" "$2"
            echo "Added remote '$PUBLIC_REMOTE' -> $2"
        fi
    else
        echo ""
        echo "Next: add the public repo URL yourself, then run a dry run."
        echo "  git remote add $PUBLIC_REMOTE git@github.com:<user>/<repo>.git"
        echo "  ./push-public.sh <branch> --dry-run"
    fi

    exit 0
fi

# ---------------------------------------------------------------------------
# Argument parsing
# ---------------------------------------------------------------------------

DRY_RUN=0
COMMIT_MSG=""
SOURCE_BRANCH=""
TARGET_BRANCH=""

while [ $# -gt 0 ]; do
    case "$1" in
        --dry-run|-n)
            DRY_RUN=1
            ;;
        -m|--message)
            shift
            if [ -z "$1" ]; then
                echo "Error: -m needs a commit message."
                exit 1
            fi
            COMMIT_MSG="$1"
            ;;
        --help|-h)
            sed -n '3,26p' "$0" | sed 's/^# \{0,1\}//'
            exit 0
            ;;
        -*)
            echo "Error: unknown option '$1'."
            exit 1
            ;;
        *)
            if [ -z "$SOURCE_BRANCH" ]; then
                SOURCE_BRANCH="$1"
            elif [ -z "$TARGET_BRANCH" ]; then
                TARGET_BRANCH="$1"
            else
                echo "Error: too many arguments ('$1')."
                exit 1
            fi
            ;;
    esac
    shift
done

# Default to the branch currently checked out.
if [ -z "$SOURCE_BRANCH" ]; then
    SOURCE_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
fi

# Same name on the public side unless told otherwise.
TARGET_BRANCH=${TARGET_BRANCH:-$SOURCE_BRANCH}

# ---------------------------------------------------------------------------
# Validation
# ---------------------------------------------------------------------------

if [ ! -f "$IGNORE_FILE" ]; then
    echo "Error: $IGNORE_FILE not found in $REPO_ROOT."
    exit 1
fi

for cmd in rsync git; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        echo "Error: '$cmd' is required but not installed."
        exit 1
    fi
done

if ! git remote get-url "$PUBLIC_REMOTE" >/dev/null 2>&1; then
    echo "Error: no remote named '$PUBLIC_REMOTE'."
    echo ""
    echo "Add it first:"
    echo "  ./push-public.sh --setup git@github.com:<user>/<repo>.git"
    exit 1
fi

if ! git rev-parse --verify --quiet "refs/heads/$SOURCE_BRANCH" >/dev/null; then
    echo "Error: branch '$SOURCE_BRANCH' does not exist locally."
    exit 1
fi

if [ ! -x "$HOOK_FILE" ] || ! grep -q "$SYNC_ENV_VAR" "$HOOK_FILE" 2>/dev/null; then
    echo "Warning: the pre-push guard hook is not installed."
    echo "         Run ./push-public.sh --setup to install it."
    echo ""
fi

PUBLIC_URL="$(git remote get-url "$PUBLIC_REMOTE")"
SOURCE_SHA="$(git rev-parse --short "$SOURCE_BRANCH")"

echo "Source : $SOURCE_BRANCH ($SOURCE_SHA)"
echo "Target : $PUBLIC_REMOTE/$TARGET_BRANCH"
echo "Remote : $PUBLIC_URL"
if [ "$DRY_RUN" -eq 1 ]; then
    echo "Mode   : DRY RUN — nothing will be pushed"
fi

# ---------------------------------------------------------------------------
# Work out the current public tip
# ---------------------------------------------------------------------------

echo -e "\nFetching $PUBLIC_REMOTE..."

PARENT_SHA=""
if git fetch --quiet "$PUBLIC_REMOTE" "$TARGET_BRANCH" 2>/dev/null; then
    PARENT_SHA="$(git rev-parse FETCH_HEAD)"
    echo "Public branch '$TARGET_BRANCH' is at $(git rev-parse --short "$PARENT_SHA")"
else
    echo "Public branch '$TARGET_BRANCH' does not exist yet — this will create it."
fi

# ---------------------------------------------------------------------------
# Build the filtered tree
# ---------------------------------------------------------------------------

TEMP_DIR=$(mktemp -d)
SRC_DIR="$TEMP_DIR/src"
STAGE_DIR="$TEMP_DIR/stage"
EXCLUDE_FILE="$TEMP_DIR/exclude_patterns.txt"

cleanup() {
    git worktree remove --force "$SRC_DIR" >/dev/null 2>&1 || true
    rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

echo -e "\nReading exclusion patterns from $IGNORE_FILE..."

pattern_count=0
while IFS= read -r line || [ -n "$line" ]; do
    pattern="${line%%$'\r'}"
    pattern="$(echo "$pattern" | sed -e 's/[[:space:]]*$//')"
    if [ -z "$pattern" ] || [ "${pattern:0:1}" = "#" ]; then
        continue
    fi

    echo "$pattern" >> "$EXCLUDE_FILE"
    pattern_count=$((pattern_count + 1))
    if [ "$DRY_RUN" -eq 1 ]; then
        echo "Will exclude: $pattern"
    fi
done < "$IGNORE_FILE"

# .git itself must never be copied into the staging tree.
echo ".git" >> "$EXCLUDE_FILE"

if [ "$DRY_RUN" -eq 0 ]; then
    echo "Loaded $pattern_count patterns."
fi

# Check out the branch tip into a detached worktree. Reading from a worktree
# rather than the working directory means uncommitted local changes can never
# be published by accident.
echo -e "\nExporting $SOURCE_BRANCH ($SOURCE_SHA)..."
git worktree add --detach --quiet "$SRC_DIR" "$SOURCE_BRANCH"

echo "Filtering..."
mkdir -p "$STAGE_DIR"
rsync -a --exclude-from="$EXCLUDE_FILE" "$SRC_DIR/" "$STAGE_DIR/"

total_before=$(find "$SRC_DIR" -type f -not -path "*/.git/*" | wc -l | tr -d ' ')
total_after=$(find "$STAGE_DIR" -type f | wc -l | tr -d ' ')
removed=$((total_before - total_after))

echo "Files: $total_before in branch, $total_after published, $removed held back."

# ---------------------------------------------------------------------------
# Verify nothing on the exclusion list survived
# ---------------------------------------------------------------------------

echo -e "\nVerifying..."

leaks=0
while IFS= read -r pattern; do
    [ "$pattern" = ".git" ] && continue

    # Strip the anchoring slash for the search; rsync already applied the real
    # semantics, this is a belt-and-braces check on the result.
    bare="${pattern#/}"
    bare="${bare%/}"

    if [ -e "$STAGE_DIR/$bare" ]; then
        echo "  LEAK: $bare is still present!"
        leaks=$((leaks + 1))
    fi
done < "$EXCLUDE_FILE"

# Catch anything that slipped through at a deeper level too.
for risky in ".env" "auth.json" "CLAUDE.md"; do
    found=$(find "$STAGE_DIR" -name "$risky" | head -3)
    if [ -n "$found" ]; then
        echo "  LEAK: found $risky:"
        echo "$found" | sed 's|'"$STAGE_DIR"'|  ...|'
        leaks=$((leaks + 1))
    fi
done

if [ "$leaks" -gt 0 ]; then
    echo -e "\nAborted: $leaks item(s) from $IGNORE_FILE are still in the tree."
    exit 1
fi

echo "  Clean — nothing from $IGNORE_FILE is in the tree."

# ---------------------------------------------------------------------------
# Dry run stops here
# ---------------------------------------------------------------------------

if [ "$DRY_RUN" -eq 1 ]; then
    echo -e "\n--- Top level of what would be published ---"
    ( cd "$STAGE_DIR" && ls -A )

    echo -e "\n--- Held back (present in branch, not published) ---"
    ( cd "$SRC_DIR" && find . -not -path "./.git*" -mindepth 1 | sed 's|^\./||' | sort ) > "$TEMP_DIR/before.txt"
    ( cd "$STAGE_DIR" && find . -mindepth 1 | sed 's|^\./||' | sort ) > "$TEMP_DIR/after.txt"
    comm -23 "$TEMP_DIR/before.txt" "$TEMP_DIR/after.txt" | awk -F/ '!seen[$1]++ || NF==1' | head -40

    echo -e "\nDry run complete. Nothing was pushed."
    echo "Run without --dry-run to publish."
    exit 0
fi

# ---------------------------------------------------------------------------
# Build the commit with plumbing — no local branch, no private parent
# ---------------------------------------------------------------------------

echo -e "\nBuilding commit..."

export GIT_INDEX_FILE="$TEMP_DIR/index"
rm -f "$GIT_INDEX_FILE"

git --work-tree="$STAGE_DIR" add -A
TREE_SHA="$(git write-tree)"
unset GIT_INDEX_FILE

# Nothing changed since the last sync? Then there is nothing to push.
if [ -n "$PARENT_SHA" ]; then
    PARENT_TREE="$(git rev-parse "${PARENT_SHA}^{tree}")"
    if [ "$TREE_SHA" = "$PARENT_TREE" ]; then
        echo "Public repo is already up to date — nothing to push."
        exit 0
    fi
fi

if [ -z "$COMMIT_MSG" ]; then
    # Prefer the plugin version for a meaningful default message.
    PLUGIN_VERSION="$(git show "$SOURCE_BRANCH:woolentor_addons_elementor.php" 2>/dev/null \
        | grep -m1 -E '^[[:space:]]*\*?[[:space:]]*Version:' \
        | sed -E 's/.*Version:[[:space:]]*//' | tr -d '[:space:]\r')"
    if [ -n "$PLUGIN_VERSION" ]; then
        COMMIT_MSG="Sync: $PLUGIN_VERSION"
    else
        COMMIT_MSG="Sync from $SOURCE_BRANCH"
    fi
fi

if [ -n "$PARENT_SHA" ]; then
    COMMIT_SHA="$(git commit-tree "$TREE_SHA" -p "$PARENT_SHA" -m "$COMMIT_MSG")"
else
    COMMIT_SHA="$(git commit-tree "$TREE_SHA" -m "$COMMIT_MSG")"
fi

echo "Commit : $(git rev-parse --short "$COMMIT_SHA")  \"$COMMIT_MSG\""

# ---------------------------------------------------------------------------
# Push
# ---------------------------------------------------------------------------

echo -e "\nPushing to $PUBLIC_REMOTE/$TARGET_BRANCH..."

env "$SYNC_ENV_VAR=1" git push "$PUBLIC_REMOTE" \
    "$COMMIT_SHA:refs/heads/$TARGET_BRANCH"

echo -e "\nPublished $total_after files to $PUBLIC_REMOTE/$TARGET_BRANCH"
echo "$removed file(s) held back per $IGNORE_FILE."
