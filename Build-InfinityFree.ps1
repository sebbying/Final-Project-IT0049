$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$PhpCommand = Get-Command php -ErrorAction SilentlyContinue
$PhpExe = if ($PhpCommand) { $PhpCommand.Source } elseif (Test-Path 'C:\xampp\php\php.exe') { 'C:\xampp\php\php.exe' } else { throw 'Install PHP or run Setup-Windows.ps1 first.' }
& $PhpExe tools/build.php
if ($LASTEXITCODE -ne 0) { throw 'The hosting folder could not be built.' }
