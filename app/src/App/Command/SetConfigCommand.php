<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 20/03/18
 * Time: 16:56
 */

namespace App\Command;


use App\Model\ApplicationCommand;
use App\Service\Tools;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetConfigCommand extends ApplicationCommand
{
	const REQUIRED_OPTIONS = [Tools::IP];

	const OPTIONAL_OPTIONS = [
		Tools::MODULE_CLOUD_DNS,
		Tools::MODULE_CLOUD_SERVER,
		Tools::MODULE_CLOUD_STATE,
		Tools::MODULE_RESET_PERSISTENCE,
		Tools::MODULE_IP,
		Tools::MODULE_SUBNET_MASK,
		Tools::MODULE_GATEWAY,
	];

	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		$this
			->setName('config:set')
			->setDescription('Set config for device at given IP')
			->setHelp('config:get --ip=X.X.X.X [--' . Tools::MODULE_IP . '=X.X.X.X] [--' . Tools::MODULE_SUBNET_MASK . '=X.X.X.X] [--' . Tools::MODULE_GATEWAY . '=X.X.X.X] [--' . Tools::MODULE_RESET_PERSISTENCE . '=on|1|true|ON|TRUE|off|0|false|OFF|FALSE] [--' . Tools::MODULE_CLOUD_DNS . '=X.X.X.X] [--' . Tools::MODULE_CLOUD_SERVER . '=X.X.X.X] [--' . Tools::MODULE_CLOUD_STATE . '=1]')
			->addOption(
				Tools::IP,
				'i',
				InputOption::VALUE_REQUIRED,
				'The relay IP to set config from. Must be a valid IPv4 or IPv6'
			)
			->addOption(
				Tools::MODULE_IP,
				'mi',
				InputOption::VALUE_REQUIRED,
				'The new IP to set for the module. Must be a valid IPv4 or IPv6'
			)
			->addOption(
				Tools::MODULE_SUBNET_MASK,
				'msm',
				InputOption::VALUE_REQUIRED,
				'The new subnet mask to set for the module. Must be a valid IPv4 or IPv6'
			)
			->addOption(
				Tools::MODULE_GATEWAY,
				'mg',
				InputOption::VALUE_REQUIRED,
				'The new gateway IP address to set for the module. Must be a valid IPv4 or IPv6'
			)
			->addOption(
				Tools::MODULE_RESET_PERSISTENCE,
				'mrp',
				InputOption::VALUE_REQUIRED,
				'Wether the module have to retain relay states across reset (power failure...)'
			)
			->addOption(
				Tools::MODULE_CLOUD_DNS,
				'mcd',
				InputOption::VALUE_REQUIRED,
				'The new DNS server address for resolving cloud server.'
			)
			->addOption(
				Tools::MODULE_CLOUD_SERVER,
				'mcs',
				InputOption::VALUE_REQUIRED,
				'The new cloud server address.'
			)
			->addOption(
				Tools::MODULE_CLOUD_STATE,
				'mcst',
				InputOption::VALUE_REQUIRED,
				'The new cloud server feature state.'
			)
		;
	}

	/**
	 * @inheritdoc
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Check mandatory options
		array_map(function($optionName) use($input) {
			if(null === $input->getOption($optionName)) {
				throw new InvalidOptionException("option \"{$optionName}\" is required");
			}
		}, static::REQUIRED_OPTIONS, ['input' => $input]);

		// Business checks around options
		if (!filter_var($input->getOption(Tools::IP), FILTER_VALIDATE_IP)) {
			throw new InvalidOptionException('IP must be a valid IP format (IPv4 or IPv6)');
		}

		// Update
		array_map(function($option) use($input) {
			if(null !== $input->getOption($option)) {
				$this->container['tools']->setConfig(
					$input->getOption(Tools::IP),
					[
						'name' => $option,
						'value'=> $input->getOption($option)
					]
				);
			}
		}, static::OPTIONAL_OPTIONS);

		$this->container['tools']->saveConfig($input->getOption(Tools::IP));


//		$output->writeln(json_encode($moduleData));
	}

}