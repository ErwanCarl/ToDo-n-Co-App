<?php 

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginSubscriber extends AbstractController implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator) 
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LogoutEvent::class => 'onLogout'
        ];
    }

    public function onLoginSuccess(): void
    {
        $this->addFlash(
            'success',
            'Vous êtes désormais connecté.'
        );
    }

    public function onLogout(): void
    {
        $this->addFlash(
            'success',
            'Vous êtes désormais déconnecté.'
        );
    }
}
