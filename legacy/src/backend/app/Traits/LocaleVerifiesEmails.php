<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

/**
 * Trait LocaleVerifiesEmails copy Laravel Trait
 * @package App\Traits
 */
trait LocaleVerifiesEmails
{
    use RedirectsUsers;

    /**
     * Show the email verification notice.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect($this->redirectPath())
            : view('auth.verify');
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param Request $request
     * @return Application|RedirectResponse|Response|Redirector
     *
     * @throws AuthorizationException
     */
    public function verify(Request $request)
    {
        if (!hash_equals((string)$request->route('id'), (string)$request->user()->getKey())) {
            throw new AuthorizationException;
        }

        $hash = $request->request->get('hash');
        if (!hash_equals((string)$hash, sha1($request->user()->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new Response('', 204)
                : redirect($this->redirectPath());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        if ($response = $this->verified($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect($this->redirectPath())->with('verified', true);
    }

    /**
     * The user has been verified.
     *
     * @param Request $request
     * @return mixed
     */
    protected function verified(Request $request)
    {
        //
    }

    /**
     * Resend the email verification notification.
     *
     * @param Request $request
     * @return Application|RedirectResponse|Response|Redirector
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new Response('', 204)
                : redirect($this->redirectPath());
        }

        $request->user()->sendEmailVerificationNotification();


        return $request->wantsJson()
            ? new Response('', 202)
            : back()->with('resent', true);
    }
}
