<?php

namespace App\EventSubscribers;

use App\Interfaces\AuthenticatedControllerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AuthenticatedSubscriber
 * @package App\EventSubscribers
 */
class AuthenticatedSubscriber implements EventSubscriberInterface
{
    public const KEY_FIELD = 'key';
    private const KEYS = ['_n5ctwQwSm'];

    /**
     * @var string[]
     */
    private $keys = [];

    /**
     * AuthenticatedSubscriber constructor.
     */
    public function __construct()
    {
        $this->keys = self::KEYS;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AuthenticatedControllerInterface) {
            $key = $event->getRequest()->get(self::KEY_FIELD, '');

            if (!in_array($key, $this->keys)) {
                throw new AccessDeniedException('Invalid key');
            }
        }
    }
}