<?php namespace Naraki\System\Controllers;

use Naraki\Core\Controllers\Admin\Controller;
use Naraki\System\Requests\UpdateSettings;
use Naraki\System\Requests\UpdateSitemapSettings;
use Naraki\System\Requests\UpdateSocialSettings;
use Naraki\Core\Support\Frontend\Jsonld\Models\General as GeneralJsonldManager;
use Naraki\Core\Support\Frontend\Social\General as SocialTagManager;
use Illuminate\Http\Response;
use Naraki\System\Support\Settings as SettingsDataObject;

class Settings extends Controller
{

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit()
    {
        return response([
            'settings' => SettingsDataObject::general(false),
            'websites' => GeneralJsonldManager::websiteList(),
            'organizations' => GeneralJsonldManager::organizationList()
        ], Response::HTTP_OK);
    }

    /**
     * @param \Naraki\System\Requests\UpdateSettings $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(UpdateSettings $request)
    {
        $input = $request->all();
        $settings = new SettingsDataObject();
        if (!is_null($input['logo'])) {
            $file = $request->file('logo');
            $filename = sprintf('logo_jld.%s', $file->getClientOriginalExtension());
            $file->move(sprintf('%s/media/img/site', public_path()), $filename);
            $input['logo'] = asset(sprintf('media/img/site/%s', $filename));
        }
        if (!is_array($input['links'])) {
            $input['links'] = explode(',', $input['links']);
        }

        SettingsDataObject::saveSettings('general', $input);
        $settings->put('has_jsonld', $input['jsonld']);
        $settings->put('meta_jsonld',
            ($input['jsonld'] === true)
                ? GeneralJsonldManager::makeStructuredData($input)
                : ''
        );
        $settings->put('meta_robots', $input['robots'] === true ? 'index, follow' : 'noindex, nofollow');
        $settings->put('meta_description', $input['site_description']);
        $settings->put('meta_keywords', $input['site_keywords']);
        $settings->put('meta_title', $input['site_title']);
        $settings->save('general_formatted');

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function editSocial()
    {
        return response([
            'settings' => SettingsDataObject::social(false),
        ], Response::HTTP_OK);
    }

    /**
     * @param \Naraki\System\Requests\UpdateSocialSettings $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateSocial(UpdateSocialSettings $request)
    {
        $settings = new SettingsDataObject();
        $generalSettings = SettingsDataObject::getSettings('general');
        $input = $request->all();
        SettingsDataObject::saveSettings('social', $input);

        $description = $generalSettings->get('site_description');
        $title = $generalSettings->get('site_title');
        $settings->put('has_facebook', $input['open_graph']);
        $settings->put('has_twitter', $input['twitter_cards']);
        if ($input['open_graph'] === true) {
            $settings->put('meta_facebook', SocialTagManager::getFacebookTagList($title, $description, $input));
        }
        if ($input['twitter_cards'] === true) {
            $settings->put('meta_twitter', SocialTagManager::getTwitterTagList($title, $description, $input));
        }
        $settings->save('social_formatted');

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function editSitemap()
    {
        return response([
            'settings' => SettingsDataObject::sitemap(false),
        ], Response::HTTP_OK);
    }

    /**
     * @param \Naraki\System\Requests\UpdateSitemapSettings $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateSitemap(UpdateSitemapSettings $request)
    {
        SettingsDataObject::saveSettings('sitemap', $request->all());
        return response(null, Response::HTTP_NO_CONTENT);
    }


}