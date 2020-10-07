<?php
declare(strict_types=1);

namespace uhc\game\scenario;

use uhc\Loader;
use Throwable;
use function is_array;
use function is_dir;
use function mkdir;
use function scandir;
use function str_replace;
use function substr;

class ScenarioManager{
	/** @var Loader */
	private Loader $plugin;
	/** @var Scenario[] */
	private array $registeredScenarios = [];

	public function __construct(Loader $plugin)
	{
		$this->plugin = $plugin;
		$this->loadDirectoryScenarios($plugin->getDataFolder() . "scenarios/");
	}

	public function loadDirectoryScenarios(string $directory): void
	{
		if (!is_dir($directory)) {
			mkdir($directory);
		}
		$dir = scandir($directory);
		if (is_array($dir)) {
			foreach ($dir as $file) {
				if (substr($file, -4) === ".php") {
					$fileLocation = $directory . $file;
					try {
						require($fileLocation);
						$class = "\\" . str_replace(".php", "", $file);
						if (($scenario = new $class($this->plugin)) instanceof Scenario) {
							$this->registerScenario($scenario);
						}
					} catch (Throwable $error) {
						$this->plugin->getLogger()->error("File $file failed to load with reason: " . $error->getMessage());
					}
				}
			}
		}
	}

	/**
	 * @return Scenario[]
	 */
	public function getScenarios(): array
	{
		return $this->registeredScenarios;
	}

	public function getScenarioByName(string $name) : ?Scenario
	{
		return isset($this->registeredScenarios[$name]) ? $this->registeredScenarios[$name] : null;
	}

	public function registerScenario(Scenario $scenario): void
	{
		if(isset($this->registeredScenarios[$scenario->getName()])){
			$this->plugin->getLogger()->notice("Ignored duplicate scenario: {$scenario->getName()}");
		}
		$this->registeredScenarios[$scenario->getName()] = $scenario;
	}

	public function unregisterScenario(Scenario $scenario): void
	{
		unset($this->registeredScenarios[$scenario->getName()]);
	}
}
