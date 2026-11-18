<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\Post;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Notifies post's author about new comments.
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
final readonly class CommentNotificationSubscriber implements EventSubscriberInterface
{
    private const PENDING_NOTIFICATION = '_comment_notification';

    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private RequestStack $requestStack,
        private LoggerInterface $logger,
        #[Autowire('%app.notifications.email_sender%')]
        private string $sender,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();

        /** @var Post $post */
        $post = $comment->getPost();

        /** @var User $author */
        $author = $post->getAuthor();

        /** @var string $emailAddress */
        $emailAddress = $author->getEmail();

        $linkToPost = $this->urlGenerator->generate('blog_post', [
            'slug' => $post->getSlug(),
            '_fragment' => 'comment_'.$comment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->translator->trans('notification.comment_created');

        $body = $this->translator->trans('notification.comment_created.description', [
            'title' => $post->getTitle(),
            'link' => $linkToPost,
        ]);

        // See https://symfony.com/doc/current/mailer.html
        $email = new Email()
            ->from($this->sender)
            ->to($emailAddress)
            ->subject($subject)
            ->html($body)
        ;

        // Send the e-mail once the response has been sent, so the reader does not wait for the mailer.
        // In config/packages/mailer.yaml the delivery of messages is disabled in the development environment.
        // That's why you won't actually receive any email: the log shows each notification sent.
        $commentId = $comment->getId();
        // Keep the pending notification on the request: it is sent by onKernelTerminate() once the response has been sent.
        $this->requestStack->getCurrentRequest()?->attributes->set(self::PENDING_NOTIFICATION, [
            'email' => $email,
            'to' => $emailAddress,
            'comment' => $commentId,
        ]);
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $notification = $request->attributes->get(self::PENDING_NOTIFICATION);
        if (null === $notification) {
            return;
        }
        $request->attributes->remove(self::PENDING_NOTIFICATION);

        $this->mailer->send($notification['email']);
        $this->logger->notice('Comment notification sent to {to} for comment #{comment}.', [
            'to' => $notification['to'],
            'comment' => $notification['comment'],
        ]);
    }
}
