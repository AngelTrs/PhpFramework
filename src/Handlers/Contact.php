<?php
/**
 * Created by PhpStorm.
 * User: Angel for A_TRS Apps (angeltorres.xyz)
 * Date: 10/22/2019
 * Time: 6:02 PM
 */

namespace AngelTrs\PhpFramework\Handlers;


use AngelTrs\PhpFramework\View\RendererInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Zend\Diactoros\Response\RedirectResponse;

class Contact
{
    private $request;
    private $response;
    private $renderer;
    private $mailer;
    private $settings;

    public function __construct(RequestInterface $request, ResponseInterface $response, RendererInterface $renderer, Mailer $mailer, $settings)
    {
        $this->request = $request;
        $this->response = $response;
        $this->renderer = $renderer;
        $this->mailer = $mailer;
        $this->settings = $settings;
    }

    public function index() {

        $errors = (isset($_SESSION['contact_errors'])) ? $_SESSION['contact_errors'] : [];
        $success = (isset($_SESSION['contact_success'])) ? $_SESSION['contact_success'] : null;

        $this->response->withHeader('Content-Type', 'text/html');
        $htmlContent = $this->renderer->render('contact', [
            'activePage' => 'contact', 'errors' => $errors, 'success' => $success,
            ]);

        unset($_SESSION['contact_errors']);
        unset($_SESSION['contact_success']);

        $this->response->getBody()->write($htmlContent);
        return $this->response;
    }

    public function send() {

        $postContent = $this->request->getParsedBody();

        if (!empty($postContent['token'])) {
                if (!hash_equals($_SESSION['token'], $postContent['token'])) {
                    $_SESSION['contact_errors'] = ["Invalid submission. Please try again."];
                    $this->response = new RedirectResponse('/contact');
            }
        }

        // TODO: Validate input

        $htmlContent = $this->renderer->render('contact_email', ['postContent' => $postContent]);

        $email = (new Email())
            ->from($postContent['email'])
            ->to($this->settings['contactEmail'])
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Masonic TFT Website Contact')
            //->text('email goes here')
            ->html($htmlContent);

        $this->mailer->send($email);

        $_SESSION['contact_success'] = "Message Sent! We appreciate you contacting us and will get back to you shortly.";

        return new RedirectResponse('/contact');
    }


}