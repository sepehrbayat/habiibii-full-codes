#!/bin/bash

# Graphiti Task Wrapper Script
# اسکریپت wrapper برای کارهای Graphiti
# 
# This script wraps each task to automatically update Graphiti knowledge base
# این اسکریپت هر کار را wrap می‌کند تا به طور خودکار پایگاه دانش Graphiti را به‌روزرسانی کند

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KNOWLEDGE_BASE="$SCRIPT_DIR/graphiti-knowledge-base.json"
SYNC_SCRIPT="$SCRIPT_DIR/graphiti-sync-workflow.php"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if knowledge base exists
if [ ! -f "$KNOWLEDGE_BASE" ]; then
    log_error "Knowledge base file not found: $KNOWLEDGE_BASE"
    exit 1
fi

# Get task name from arguments or use default
TASK_NAME="${1:-Unknown Task}"
shift || true

# Execute the actual task (remaining arguments)
log_info "Executing task: $TASK_NAME"
log_info "Command: $@"

# Run the task and capture output
TASK_OUTPUT=$(eval "$@" 2>&1)
TASK_EXIT_CODE=$?

if [ $TASK_EXIT_CODE -eq 0 ]; then
    log_info "Task completed successfully"
    
    # Update knowledge base
    log_info "Updating Graphiti knowledge base..."
    
    # Extract affected files from git status if available
    AFFECTED_FILES=$(git status --porcelain 2>/dev/null | awk '{print $2}' | jq -R -s -c 'split("\n")[:-1]' 2>/dev/null || echo "[]")
    
    # Update knowledge base using PHP script
    if [ -f "$SYNC_SCRIPT" ]; then
        php "$SYNC_SCRIPT" update "$TASK_NAME" '[]' "$AFFECTED_FILES" || log_warn "Failed to update knowledge base"
    else
        log_warn "Sync script not found, skipping knowledge base update"
    fi
    
    # Try to sync with Graphiti server
    log_info "Attempting to sync with Graphiti server..."
    if [ -f "$SYNC_SCRIPT" ]; then
        php "$SYNC_SCRIPT" sync || log_warn "Failed to sync with Graphiti server (may not be running)"
    fi
    
    log_info "Task workflow completed"
else
    log_error "Task failed with exit code: $TASK_EXIT_CODE"
    echo "$TASK_OUTPUT"
    exit $TASK_EXIT_CODE
fi

# Display task output
echo "$TASK_OUTPUT"

