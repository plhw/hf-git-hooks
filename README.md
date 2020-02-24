# HF Git Hooks

Tool to automaticly installs hooks for repository in your `./.git/hooks` path.

Currently we have a pre-push hook that does the following;

- Checks for a valid composer.json and composer.lock.
- Check code style

### INSTALLATION

`composer require plhw/hf-git-hooks --dev` as a composer dependency.

### Configuration

Add the following to your composer.json file.

```
"scripts": {
   	"pre-update-cmd": "HF\\GitHooks\\Installer::preHooks",
   	"pre-install-cmd": "HF\\GitHooks\\Installer::preHooks",
	"post-update-cmd": "HF\\GitHooks\\Installer::postHooks",
	"post-install-cmd": "HF\\GitHooks\\Installer::postHooks"
}
```
