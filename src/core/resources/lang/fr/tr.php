<?php

return [
    'auth' => [
        'failed' => 'Ces identifiants sont introuvables, ou votre compte n\'a pas encore été activé.',
        'failed_not_allowed' => 'Votre compte n\'est pas habilité à accéder le backoffice. Veuillez contacter votre administrateur.',
        'throttle' => 'Le nombre de tentatives de connexion a été dépassé. Merci d\'essayer de nouveau dans :seconds secondes.',
        'alerts' => [
            'registered_title' => 'Création de compte terminée',
            'registered_body' => 'Merci ! Nous vous avons envoyé un e-mail contenant les instructions vous permettant d\'activer votre compte.',
            'activated_title' => 'Activation de compte terminée',
            'activated_body' => 'Nous avons activé votre compte, merci de bien vouloir vous connecter.',
            'activation_error_title' => 'Erreur lors de l\'activation du compte',
            'activation_error_body' => 'Il semblerait que le lien d\'activation n\'est plus valide. Le compte a probablement déjà été activé. Essayez de vous connecter.',
            'account_deleted_title' => 'Compte supprimé',
            'account_deleted_body' => 'Votre compte a été supprimé.',
            'email_title' => 'Réinitialisation du mot de passe',
            'email_body' => 'Merci de bien vouloir saisir votre e-mail. Nous vous enverrons un lien par e-mail vous permettant de définir un nouveau mot de passe.',
            'reset_title' => 'Votre mot de passe a été réinitialisé !',
            'reset_body' => 'Vous pouvez vous connecter en utilisant votre nouveau mot de passe.',
            'recaptcha_title' => 'Oups, une erreur s\'est produite !',
            'recaptcha_body' => 'Nous n\'avons pas pu valider le formulaire via Google Recaptcha. Merci de tenter de renvoyer le formulaire dans quelques instants.',
            'email_reset_title' => 'Presque terminé !',
            'email_reset_body' => 'Merci d\'entrer votre nouveau mot de passe ci-dessous.',
        ],
        'content' => [
            'email' => 'Adresse e-mail',
            'send_link' => 'Envoyer le lien de réinitialisation de mot de passe',
        ],
        'create_account' => 'Créer un compte',
        'login_account' => 'Connexion à votre compte',
        'register_username_help' => 'Peut contenir des lettres, nombres et underscores. Doit contenir entre 8 et 25 caractères.',
        'email_address' => 'Adresse e-mail',
        'password' => 'Mot de passe',
        'login' => 'Connexion',
        'register_account' => 'Créér un compte',
        'remember_me' => 'Se souvenir de moi',
        'forgot_password' => 'Mot de passe oublié ?',
        'password_help' => 'Doit contenir au moins 8 caractères.',
        'hide_password' => 'Cacher le mot de passe',
        'show_password' => 'Montrer le mot de passe',
        'required_fields' => 'Les champs marqués avec un astérisque (*) sont obligatoires.',
        'register' => 'Créer un compte',
    ],
    'routes' => [
        'home' => '/',
        'admin_login' => 'connexion',
        'login' => 'connexion',
        'activate' => 'compte/activer/{token}',
        'register' => 'compte',
        'password_reset' => 'mdp/reinitialisation',
        'password_email' => 'mdp/email',
        'password_reset_token' => 'mdp/reinitialisation/{token}/{email}',
        'settings_profile' => 'parametres/profil',
        'settings_notifications' => 'parametres/notifications',
        'settings_account' => 'parametres/compte',
        'contact' => 'contact',
        'user' => 'utilisateur/{slug}',
        'search' => 'recherche/{q?}',
        'privacy' => 'vie-privee',
        'terms_service' => 'termes-utilisation',
    ],
    'jsonld' => [
        'organizations' => [
            'Airline' => 'Compagnie aérienne',
            'AnimalShelter' => 'Refuge pour animaux',
            'AutomotiveBusiness' => 'Industrie automobile',
            'ChildCare' => 'Garde d\'enfants',
            'Corporation' => 'Entreprise',
            'Dentist' => 'Dentiste',
            'DryCleaningOrLaundry' => 'Nettoyage à sec ou blanchisserie',
            'EducationalOrganization' => 'Organisme educatif',
            'EmergencyService' => 'Service d\'urgence',
            'EmploymentAgency' => 'Agence d\'emploi',
            'EntertainmentBusiness' => 'Industrie du divertissement',
            'FinancialService' => 'Services financiers',
            'FoodEstablishment' => 'Établissement alimentaire',
            'GovernmentOffice' => 'Bureau gouvernemental',
            'GovernmentOrganization' => 'Organisation gouvernementale',
            'HealthAndBeautyBusiness' => 'Industrie de la santé / Industrie cosmétique',
            'HomeAndConstructionBusiness' => 'Industrie de la construction',
            'InternetCafe' => 'Internet café',
            'LegalService' => 'Services légaux',
            'Library' => 'Librairie',
            'LocalBusiness' => 'Commerce local',
            'LodgingBusiness' => 'Industrie du logement',
            'MedicalOrganization' => 'Organisation médicale',
            'NewsMediaOrganization' => 'Organisations médiatiques',
            'NGO' => 'ONG',
            'Organization' => 'Organisation',
            'PerformingGroup' => 'Groupe d\'interprètes',
            'ProfessionalService' => 'Service professionnel',
            'RadioStation' => 'Station radio',
            'RealEstateAgent' => 'Agent immobilier',
            'RecyclingCenter' => 'Centre de recyclage',
            'SelfStorage' => 'Stockage libre-service',
            'ShoppingCenter' => 'Centre commercial',
            'SportsActivityLocation' => 'Centre d\'activité sportive',
            'SportsOrganization' => 'Organisation sportive',
            'Store' => 'Magasin',
            'TelevisionStation' => 'Chaine de télévision',
            'TouristInformationCenter' => 'Centre d\'information touristique',
            'TravelAgency' => 'Agence de voyages',
        ],
        'websites' => [
            'WebSite' => 'Site web',
            'Blog' => 'Blog',
        ],
    ],

];