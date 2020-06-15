# UHC
Popular Ultra-Hardcore gamemode brought to PocketMine!

## Features
This UHC plugin currently has the following features:
- Borders (experimental bedrock borders)
- Scenarios (You can add your own!)
- UHC Phases (Countdown, Grace, PvP, and Normal)
- Commands (GlobalMute, Heal, Scenarios, Spectator, Tpall, and UHC)
with more on the way!

## FAQ
### There's no scenarios?
When making this, I still wanted each UHC server to have their own unique scenarios, with the same basic UHC concept. However, I know not everyone knows how to make scenarios, so I released a [scenario pack](https://github.com/Wumpotamus/UHC/releases), however scenarios are still a work in progress, and may need updated from time to time. You can unpack these scenarios in plugin_data/UHC/scenarios. Zip file support on the [way!](https://github.com/Wumpotamus/UHC/issues/6)<br>
*Note: the versioning for scenarios packs match up with the plugin version!*

### Okay... I got scenarios installed, now what?
After you install scenarios, you simply start your server, and enable your scenarios by doing `/scenarios` or `/sc` for short! When you're satisfied with the amount of players, just run `/uhc` and it'll start!

### Is there multiworld support?
No, I do not plan on having multiworld support either. If you try and run the UHC on another world, you will run into issues with borders and scattering.

### I want a feature, how can I request it?
Feel free to open up an issue about any features you want! I will try my best to add anything except for scenarios.

### How do I make my own scenarios?
Making your own scenarios is simple! You'll want to create a `.php` file, with no namespace, as shown below.<br>

```php
<?php
declare(strict_types=1);

use uhc\Loader;
use uhc\game\Scenario;

class ExampleScenario extends Scenario{
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "ExampleScenario");
    }

    public function handleEvent(ExampleEvent $ev){
        //code
    }
}
```
**Note: Scenario API is presently not stable and is subject to change!**

### How can I add health under nametags?
As you may have noticed, as of the recent commit, health under nametags have gone missing. <br>
It was not unintentional, if you'd like the health back (and more!), use [PlayerTags](https://github.com/sylvrs/PlayerTags).
## Current Issues
- Scattering can cause some lag for a few seconds.
- Bedrock borders cause lag when built above size 100. 
Both of these problems are well-known, just been neglected, closure tasks can help reduce the issue, however I do not have much ability to test with large amounts of players.