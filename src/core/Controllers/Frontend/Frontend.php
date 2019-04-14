<?php namespace Naraki\Core\Controllers\Frontend;

use Naraki\Sentry\Events\PersonSentContactRequest;
use Naraki\Mail\Support\Requests\SendContactEmail;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Naraki\Mail\Jobs\SubscribeToNewsletter;

class Frontend extends Controller
{
    use DispatchesJobs;

    public function contact()
    {
        return view('core::frontend.site.contact');
    }

    public function sendContactEmail(SendContactEmail $request)
    {
        event(new PersonSentContactRequest(
                $request->get('sender_email'),
                $request->get('email_subject'),
                $request->get('email_body')
            )
        );
        return redirect(route_i18n('home'))->with(
            'msg',
            ['type' => 'success', 'title' => trans('messages.contact_send_success')]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function newsletterSubscribe(Request $request)
    {
        $input = $request->only('full_name', 'email');
        if (isset($input['full_name']) && isset($input['email'])) {
            $this->dispatch(new SubscribeToNewsletter($input));
        }
        return response([
            'title' => trans('titles.subscribed_msg_title'),
            'text' => trans('titles.subscribed_msg_text')
        ], Response::HTTP_OK);
    }

    public function privacy()
    {
        return view('core::frontend.site.privacy');
    }

    public function termsOfService()
    {

        return view('core::frontend.site.terms_service');
    }

}