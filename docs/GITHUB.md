# Upload the raw project to GitHub

Do this after completing the local setup. That produces the installed starter configuration and a real `composer.lock`. The application source is committed; dependencies are restored from Composer during setup and bundled separately for InfinityFree.

1. Install [Git for Windows](https://git-scm.com/downloads) if needed. Open PowerShell in `C:\xampp\htdocs\pos-codeigniter`.
2. On [GitHub](https://github.com/new), create a repository named `it0049-codeigniter-pos`. Choose the visibility your instructor requires. Leave initialization options for README, license, and gitignore unchecked because these files are already supplied.
3. Run the commands below, replacing `YOUR_USERNAME` with your GitHub username:

   ```powershell
   git init
   git branch -M main
   git add .
   git check-ignore .env
   git status --short
   git commit -m "Build complete CodeIgniter POS system"
   git remote add origin https://github.com/YOUR_USERNAME/it0049-codeigniter-pos.git
   git push -u origin main
   ```

   `git check-ignore .env` should print `.env`. Review `git status --short` before committing: `.env`, `.tools/`, `vendor/`, `writable/` contents, and `dist/` must not be staged. The safe `.env.example` and `.env.infinityfree.example` templates should be staged, along with `composer.lock` and `database/infinityfree.sql`.

4. If Git asks for your name and email, configure the identity you want recorded on your commits:

   ```powershell
   git config user.name "YOUR NAME"
   git config user.email "YOUR GITHUB EMAIL OR NOREPLY ADDRESS"
   ```

   Then repeat the commit and push. Sign in through Git's browser prompt if requested; GitHub account passwords are not used as HTTPS Git passwords.

5. Open the repository and confirm that it contains `app/`, `public/`, `tools/`, `tests/`, `database/`, `.github/`, `composer.json`, `composer.lock`, `spark`, and `README.md`. You are submitting raw source files, so do not upload only the ZIP.
6. Open **Actions → POS checks** and review both PHP versions. Fix any reported failures before submission. If Actions are disabled by your school, run the provided tests locally and keep the results.
7. Deploy using `INFINITYFREE.md`. Add your HTTPS URL to GitHub's About → Website field and update `SUBMISSION.md`:

   ```powershell
   git add SUBMISSION.md
   git commit -m "Add submission links"
   git push
   ```

For later application changes: edit the source, check it locally, commit/push the raw files, rebuild the hosting folder, and upload the changed application files. Keep production credentials only in the server's `.env` and the ignored staging copy. On code updates, preserve the server's `writable/` directory and `.env`.

The ZIP's `.gitignore` excludes credentials before your first commit. If you accidentally committed a real password, rotate it; adding it to `.gitignore` afterwards does not remove it from Git history.
