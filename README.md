# AutoPluginUpdater 🌐

> This is the AutoUpdaterPlugin, it can update your plugins automatically (if they use AutoUpdaterPlugin API) 

## Community:
**Discord:**

<a href="https://discord.gg/HkfMbBN2AD"><img src="https://img.shields.io/discord/982037265075302551?label=discord&color=7289DA&logo=discord" alt="Discord"></a>

## Dependence ⚠️

### SmartCommand:
> This plugin depends on [SmartCommand Framework](https://github.com/RajadorDev/SmartCommand). Currently, only utilities from the SmartCommand system are being used, but soon AutoPluginUpdater will include commands.

## How it works ⚙

This system updates plugins automatically based on their releases published on GitHub. Your release must include a `phar` file and use the correct release tag according to the version (for example: 1.0.0, 1.0.1, 1.1.0, etc.). The system will then look for the most recent release compared to the current plugin version installed on the server and update it automatically (according to the configuration settings).

## API 📰

You can create any kind of automatic update method using AutoPluginUpdater classes, but the default one (via `GitHub API`) works like this:

- First set AutoUpdaterPlugin as your plugin dependence in `plugin.yml`:

`plugin.yml`:
```yml
depend: AutoPluginUpdater
```

- Now you need to schedule your plugin update check using your github repository:

In your `Main` file:
```php
<?php

namespace MyPlugin;

use pocketmine\plugin\PluginBase;
use rajadordev\autoupdater\api\CheckUpdateScheduler;
use rajadordev\autoupdater\api\plugin\defaults\github\GitHubPluginUpdaterAPI;
use rajadordev\autoupdater\api\PluginUpdaterChecker;

class MyPluginMain extends PluginBase
{

    public function onEnable()
    {
        CheckUpdateScheduler::getInstance()->schedule(
            PluginUpdaterChecker::create(
                $this, // Your plugin instance
                GitHubPluginUpdaterAPI::createFromPlugin(
                    $this, // Your instance again
                    'MyGitHubUsername', // Your github username
                    'MyRepository' // Your repository
                )
            )
        );
    }
}
```

**Done. The system will now check for automatic updates.**
