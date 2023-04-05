<?php

declare(strict_types=1);

namespace EMPORIKO\Libraries\Auth;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use EMPORIKO\Helpers\Strings as Str;

/**
 * Session Authentication Filter.
 *
 * Email/Password-based authentication for web applications.
 */
class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }
        $uri = service('uri');
        $uri=$uri->getSegments();
        
        if (Str::endsWith(current_url(), 'cron.php'))
        {
            $uri=['Home','cron'];
            goto check_method_access;
        }else
        if (is_array($uri) && count($uri) > 0)
        {
            if (count($uri) < 2)
            {
                $uri[1]='index';
            }
            check_method_access:
            $uri= loadModule(ucwords($uri[0]),'getAccessForMethod',[$uri[1]]);
            
            if (is_bool($uri) && !$uri)
            {
                return;
            }
        }
        
        /** @var Session $authenticator */
        $authenticator = auth()->getAuthenticator();
        if ($authenticator->isLoged()) 
        {
            return;
        }

        /*if ($authenticator->isPending()) {
            return redirect()->route('auth-action-show')
                ->with('error', $authenticator->getPendingMessage());
        }*/

        return redirect()->route('login')->with('refurl',current_url());
    }

    /**
     * We don't have anything to do here.
     *
     * @param Response|ResponseInterface $response
     * @param array|null                 $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}