#!/bin/bash
# Script to open a new Cursor window with the same workspace
# اسکریپت برای باز کردن پنجره جدید Cursor با همان workspace

WORKSPACE_FILE="$(pwd)/6ammart-laravel.code-workspace"

if [ -f "$WORKSPACE_FILE" ]; then
    echo "Opening new Cursor window with workspace..."
    echo "باز کردن پنجره جدید Cursor با workspace..."
    cursor "$WORKSPACE_FILE" &
else
    echo "Workspace file not found. Opening current directory..."
    echo "فایل workspace پیدا نشد. باز کردن دایرکتوری فعلی..."
    cursor . &
fi














































