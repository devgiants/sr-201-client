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
	const IP = 'ip';
	const CHANNEL = 'channel';
	const STATE = 'state';
	const REQUIRED_OPTIONS = [self::IP, self::CHANNEL, self::STATE ];

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
			->setHelp('switch --ip=X.X.X.X --channel=X --state=1')
			->addOption(static::IP, 'i', InputOption::VALUE_REQUIRED, 'The relay IP to switch. Must be a valid IPv4 or IPv6')
			->addOption(static::CHANNEL, 'c', InputOption::VALUE_REQUIRED, 'The channel to switch. Must be integer between 1 and 8')
			->addOption(static::STATE, 's', InputOption::VALUE_REQUIRED, 'The value to set. Must be 0 or 1, true or false, on or off')
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
		if (!filter_var($input->getOption(self::IP), FILTER_VALIDATE_IP)) {
			throw new InvalidOptionException('IP must be a valid IP format (IPv4 or IPv6)');
		}
		if(!is_numeric($input->getOption(self::CHANNEL)) || $input->getOption(self::CHANNEL) < 0 || $input->getOption(self::CHANNEL) > 8) {
			throw new InvalidOptionException('channel must be an integer and between 1 and 8');
		}
		if(!in_array(strtolower($input->getOption(self::STATE)), array_merge(self::ALLOWED_VALUES_ON, self::ALLOWED_VALUES_OFF), true)) {
			throw new InvalidOptionException('Value must be one of those : ' . implode(', ', array_merge(self::ALLOWED_VALUES_ON, self::ALLOWED_VALUES_OFF)));
		}

		// Normalize value
		if(in_array($input->getOption(self::STATE), self::ALLOWED_VALUES_ON)) {
			$state = self::ON;
		}
		else {
			$state = self::OFF;
		}

		$this->container['tools']->sendCommand(
			$input->getOption(self::IP),
			$input->getOption(self::CHANNEL),
			$state
		);
	}

}