@extends('mail::default')
@section('content')
<!--[if mso | IE]>
</td>
</tr>
</table>

<table
        align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600"
>
    <tr>
        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->


<div  style="background:#a42227;background-color:#a42227;Margin:0px auto;border-radius:16px 16px 0px 0px;max-width:600px;">

    <table
            align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#a42227;background-color:#a42227;width:100%;border-radius:16px 16px 0px 0px;"
    >
        <tbody>
        <tr>
            <td
                    style="border-bottom:8px solid #a75c23;direction:ltr;font-size:0px;padding:20px 0;padding-bottom:10px;padding-left:0px;padding-right:0px;padding-top:10px;text-align:center;vertical-align:top;"
            >
                <!--[if mso | IE]>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">

                    <tr>

                        <td
                                class="" style="vertical-align:top;width:600px;"
                        >
                <![endif]-->

                <div
                        class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"
                >

                    <table
                            border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"
                    >

                        <tr>
                            <td
                                    align="center" style="font-size:0px;padding:10px 25px;padding-top:20px;padding-right:0px;padding-bottom:20px;padding-left:0px;word-break:break-word;"
                            >

                                <div
                                        style="font-family:Lato, Helvetica, Arial, sans-serif;font-size:25px;line-height:27px;text-align:center;color:#ffffff;"
                                >
                                    <span style="padding-bottom: 15px">{!! $title !!}</span>
                                </div>

                            </td>
                        </tr>

                    </table>

                </div>

                <!--[if mso | IE]>
                </td>

                </tr>

                </table>
                <![endif]-->
            </td>
        </tr>
        </tbody>
    </table>

</div>


<!--[if mso | IE]>
</td>
</tr>
</table>

<table
        align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600"
>
    <tr>
        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->


<div  style="background:#efd6ce;background-color:#efd6ce;Margin:0px auto;border-radius:0px 0px 16px 16px;max-width:600px;">

    <table
            align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#efd6ce;background-color:#efd6ce;width:100%;border-radius:0px 0px 16px 16px;"
    >
        <tbody>
        <tr>
            <td
                    style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:50px;padding-left:0px;padding-right:0px;padding-top:50px;text-align:center;vertical-align:top;"
            >
                <!--[if mso | IE]>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">

                    <tr>

                        <td
                                align="center" class="" style="vertical-align:top;width:600px;"
                        >
                <![endif]-->

                <div
                        class="mj-column-per-100 outlook-group-fix" style="font-size:13px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;"
                >

                    <table
                            border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%"
                    >

                        <tr>
                            <td
                                    align="left" style="font-size:0px;padding:10px 25px;padding-top:15px;word-break:break-word;"
                            >

                                <div
                                        style="font-family:Lato, Helvetica, Arial, sans-serif;font-size:15px;line-height:1.5;text-align:left;color:#000000;"
                                >
                                    {!! trans('email.newsletter.email') !!} <a
                                            href="mailto:{!! $newsletter_email !!}" style="background:#efd6ce;color:#2a3bc6;font-family:Lato, Helvetica, Arial, sans-serif;font-size:15px;line-height:1.5;Margin:0;text-decoration:underline;text-transform:none;" target="_blank"
                                    >
                                        {!! $newsletter_email !!}
                                    </a>
                                </div>

                            </td>
                        </tr>

                        <tr>
                            <td
                                    align="left" style="font-size:0px;padding:10px 25px;padding-top:15px;word-break:break-word;"
                            >

                                <div
                                        style="font-family:Lato, Helvetica, Arial, sans-serif;font-size:15px;line-height:1.5;text-align:left;color:#000000;"
                                >
                                    {!! trans('email.newsletter.name') !!} {!! $newsletter_name !!}
                                </div>

                            </td>
                        </tr>

                    </table>

                </div>

                <!--[if mso | IE]>
                </td>

                </tr>

                </table>
                <![endif]-->
            </td>
        </tr>
        </tbody>
    </table>

</div>


<!--[if mso | IE]>
</td>
</tr>
</table>


@endsection