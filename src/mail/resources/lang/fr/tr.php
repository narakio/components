<?php

return [
    'welcome' => [
        'subject' => '[:app_name] Votre création de compte',
        'title' => 'Nous vous remercions d\'avoir ouvert un compte.',
        'body1' => 'Vous pouvez utiliser l\'adresse e-mail et le mot de passe saisis dans le formulaire de création de compte pour vous connecter.',
        'body2' => 'Si vous ne vous souvenez pas du mot de passe, vous pouvez modifier le mot de passe en cliquant sur le lien \'mot de passe oublié\' situé sous le formulaire de connexion.',
        'body3' => 'Avant de pouvoir utiliser votre compte, merci de bien vouloir cliquer sur le bouton d\'activation de compte ci-dessous.',
        'cta' => 'Activer le compte',
    ],
    'password_reset' => [
        'subject' => '[:app_name] Réinitialisation de votre mot de passe',
        'title' => 'Réinitialisation de votre mot de passe',
        'body1' => 'Nous vous envoyons cet e-mail, car nous avons reçu une demande de réinitialisation de votre mot de passe. Veuillez cliquer sur le bouton ci-dessous pour réinitialiser votre mot de passe.',
        'body2' => 'Si vous n\'avez pas effectué cette demande, vous pouvez supprimer cet e-mail.',
        'cta' => 'Réinitialiser votre mot de passe',
    ],
    'contact' => [
        'title' => 'Un message a été envoyé via le formulaire de contact.',
        'email_subject' => '[:app_name] Message de formulaire de contact reçu',
        'email' => 'Adresse e-mail :',
        'subject' => 'Sujet :',
        'message_body' => 'Corps du message :',
    ],
    'newsletter' => [
        'title' => 'Nouvelle souscription à la lettre d\'information',
        'email_subject' => '[:app_name] Nouvelle souscription à la lettre d\'information',
        'name' => 'Nom :',
        'email' => 'Adresse e-mail :',
    ],
    'origin' => 'Cet e-mail provient de la plateforme :app_name. Le message est destiné à :name (:email)',

];