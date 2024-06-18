UPGRADE FROM 2.X to 3.0
===================

## Entity

- Remove `Smart\SonataBundle\Entity\Log\BatchLog`, use `Smart\CoreBundle\Entity\ProcessTrait` and `Smart\CoreBundle\Entity\ProcessInterface` instead
- Remove `Smart\SonataBundle\Entity\Log\HistorizableInterface`, use `Smart\CoreBundle\Entity\Log\HistorizableInterface` instead
- Remove `Smart\SonataBundle\Entity\Log\HistorizableTrait`, use `Smart\CoreBundle\Entity\Log\HistorizableTrait` instead

## Logger

- Remove `Smart\SonataBundle\Logger\BatchLogger`, use `Smart\CoreBundle\Monitor\ProcessMonitor` instead
- Remove `Smart\SonataBundle\Logger\HistoryLogger`, use `Smart\CoreBundle\Logger\HistoryLogger` instead

## Security

- `SmartUserInterface` now extends `MailableInterface` so you need to define his methods

## Templates

- Remove `timeline_history_field.html.twig`, use `show_history_field.html.twig` instead
