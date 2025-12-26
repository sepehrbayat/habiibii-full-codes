# Path to your main repository
$RepoDir = "C:\laragon\www\habiibii-react"

# Path to the new vendor source
$VendorDir = "..\6ammart-react"

# Branch names
$MainBranch = "master"
$MergeBranch = "merge-vendor-update"
$BackupBranch = "$MainBranch-backup-$(Get-Date -Format 'yyyyMMddHHmmss')"

Write-Host "Starting safe merge of the vendor update..."

# 1. Checkout main branch
git -C $RepoDir checkout $MainBranch

# 2. Create backup branch
git -C $RepoDir branch $BackupBranch
Write-Host "✅ Backup of main branch created: $BackupBranch"

# 3. Create merge branch
git -C $RepoDir checkout -b $MergeBranch
Write-Host "✅ Merge branch created: $MergeBranch"

# 4. Create a temporary repository for the vendor source
$TempDir = Join-Path $env:TEMP ([System.Guid]::NewGuid().ToString())
New-Item -ItemType Directory -Path $TempDir | Out-Null
Copy-Item -Recurse -Path "$VendorDir\*" -Destination $TempDir

Set-Location $TempDir
git init -q
git add .
git commit -q -m "Vendor new version"

# 5. Add temporary remote and merge
git -C $RepoDir remote add vendor-temp $TempDir
git -C $RepoDir fetch vendor-temp
git -C $RepoDir merge vendor-temp/master --allow-unrelated-histories

# 6. Check for conflicts
$conflicts = git -C $RepoDir ls-files -u
if ($conflicts) {
    Write-Host "⚠️ Conflicts detected! Please resolve them manually."
    Write-Host "Use 'git -C $RepoDir status' to see the conflicting files."
    Write-Host "Your main branch is safe. To revert, use: git -C $RepoDir checkout $BackupBranch"
} else {
    Write-Host "✅ Merge completed successfully!"
    git -C $RepoDir commit -m "Merged vendor update" | Out-Null
    Write-Host "You can now merge '$MergeBranch' into main if desired."
}

# 7. Cleanup
Set-Location $RepoDir
git -C $RepoDir remote remove vendor-temp
Remove-Item -Recurse -Force $TempDir

Write-Host "Script finished. Please test your project thoroughly."

# Use:
# Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
# .\merge_vendor_safe.ps1
