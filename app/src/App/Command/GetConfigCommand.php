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

class GetConfigCommand extends ApplicationCommand
{
	const REQUIRED_OPTIONS = [Tools::IP];

	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		$this
			->setName('config:get')
			->setDescription('Get config for device at given IP')
			->setHelp('config:get --ip=X.X.X.X')
			->addOption(
				Tools::IP,
				'i',
				InputOption::VALUE_REQUIRED,
				'The relay IP to get config from. Must be a valid IPv4 or IPv6'
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


		$moduleData = $this->container['tools']->getConfig(
			$input->getOption(Tools::IP)
		);

		$output->writeln(json_encode($moduleData));
	}

}