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

class SwitchCommand extends ApplicationCommand
{
	const REQUIRED_OPTIONS = [Tools::IP, Tools::CHANNEL, Tools::STATE ];

	const ALLOWED_VALUES_ON = ['on', '1', 'true'];
	const ALLOWED_VALUES_OFF = ['off', '0', 'false'];

	const ON = 1;
	const OFF = 2;
	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		$this
			->setName('switch')
			->setDescription('Switch given channel relay to desired state')
			->setHelp('switch --ip=X.X.X.X --channel=1|2|3|4|5|6|7|8 --state=1|on|ON|true|TRUE|0|off|OFF|false|FALSE [--duration=X]')
			->addOption(
				Tools::IP,
				'i',
				InputOption::VALUE_REQUIRED,
				'The relay IP to switch. Must be a valid IPv4 or IPv6'
			)
			->addOption(
				Tools::CHANNEL,
				'c',
				InputOption::VALUE_REQUIRED,
				'The channel to switch. Must be integer between 1 and 8'
			)
			->addOption(
				Tools::STATE,
				's',
				InputOption::VALUE_REQUIRED,
				'The value to set. Must be 0 or 1, true or false, on or off'
			)
			->addOption(
				Tools::DURATION,
				'd',
				InputOption::VALUE_OPTIONAL,
				'The switch on duration'
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
		if(!is_numeric($input->getOption(Tools::CHANNEL)) || $input->getOption(Tools::CHANNEL) < 0 || $input->getOption(Tools::CHANNEL) > 8) {
			throw new InvalidOptionException('channel must be an integer and between 1 and 8');
		}
		if(!in_array(strtolower($input->getOption(Tools::STATE)), array_merge(static::ALLOWED_VALUES_ON, static::ALLOWED_VALUES_OFF), true)) {
			throw new InvalidOptionException('Value must be one of those : ' . implode(', ', array_merge(static::ALLOWED_VALUES_ON, static::ALLOWED_VALUES_OFF)));
		}

		if(null !== $input->getOption(Tools::DURATION) && (!is_numeric($input->getOption(Tools::DURATION)) || $input->getOption(Tools::DURATION) < 0)) {
			throw new InvalidOptionException('duration must be a positive integer');
		}

		// Normalize value
		if(in_array($input->getOption(Tools::STATE), static::ALLOWED_VALUES_ON)) {
			$state = static::ON;
		}
		else {
			$state = static::OFF;
		}

		$this->container['tools']->sendCommand(
			$input->getOption(Tools::IP),
			$input->getOption(Tools::CHANNEL),
			$state,
			$input->getOption(Tools::DURATION)
		);
	}

}