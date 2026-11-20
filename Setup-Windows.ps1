$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$PhpCommand = Get-Command php -ErrorAction SilentlyContinue
$PhpExe = if ($PhpCommand) { $PhpCommand.Source } elseif (Test-Path 'C:\xampp\php\php.exe') { 'C:\xampp\php\php.exe' } else { throw 'Install PHP 8.2+ or XAMPP, or add your PHP folder to PATH.' }
& $PhpExe tools/check.php
if ($LASTEXITCODE -ne 0) { throw 'Enable the missing extensions in the php.ini shown above and run this script again.' }
New-Item -ItemType Directory -Force .tools | Out-Null
if (!(Test-Path '.tools/composer.phar')) {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest 'https://getcomposer.org/installer' -OutFile '.tools/composer-setup.php' -UseBasicParsing
    Invoke-WebRequest 'https://composer.github.io/installer.sig' -OutFile '.tools/installer.sig' -UseBasicParsing
    $Expected = (Get-Content -LiteralPath '.tools/installer.sig' -Raw).Trim().ToLowerInvariant()
    $Actual = (Get-FileHash '.tools/composer-setup.php' -Algorithm SHA384).Hash.ToLowerInvariant()
    if ($Actual -ne $Expected) { Remove-Item '.tools/composer-setup.php'; throw 'Composer installer checksum did not match. Run the script again.' }
    & $PhpExe .tools/composer-setup.php --install-dir=.tools --filename=composer.phar
    if ($LASTEXITCODE -ne 0) { throw 'Composer could not be installed. Check your internet connection and PHP certificate settings.' }
    Remove-Item '.tools/composer-setup.php'
}
& $PhpExe .tools/composer.phar install --no-dev --prefer-dist --no-interaction
if ($LASTEXITCODE -ne 0) { throw 'Composer installation failed. Read the error above, fix it, and run this script again.' }
& $PhpExe tools/configure.php local
if ($LASTEXITCODE -ne 0) { throw 'Could not create the local .env file.' }
Write-Host ''
Write-Host 'Dependencies are installed. Next: start MySQL, edit .env, and run the migration and seeder from README.md.'
Write-Host "PHP executable: $PhpExe"
