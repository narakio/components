<?php

return [
    'email' => [
        'comment' => [
            'title' => 'Un commentaire a été posté',
            'subject' => '[:app_name] Un commentaire a été posté',
            'body1' => '__:user a commenté dans l\'article ":post".',
        ],
        'reply' => [
            'title' => 'Une personne a répondu à l\'un de vos commentaires.',
            'subject' => '[:app_name] Une personne a répondu à l\'un de vos commentaires',
            'body1' => ':user a répondu à l\'un de vos commentaires dans l\'article ":post".',
            'body2' => 'Si vous ne souhaitez pas reçevoir ces e-mails, vos options de notification peuvent être modifiées dans la zone de commentaires à côté du bouton d\'envoi de commentaires."',
        ],
        'mention' => [
            'title' => 'Une personne vous a mentionné dans un commentaire',
            'subject' => '[:app_name] Une personne vous a mentionné dans un commentaire',
            'body1' => 'Vous avez été mentionné par :user dans l\'article ":post".',
            'body2' => 'Si vous ne souhaitez pas reçevoir ces e-mails, vos options de notification peuvent être modifiées dans la zone de commentaires à côté du bouton d\'envoi de commentaires.',
            'cta' => 'Lien vers la discussion',
        ],
    ],
    'comment_add_success' => 'Votre commentaire a été envoyé, nous allons le publier dans un instant.',
    'reply_add_success' => 'Votre réponse a été envoyée, nous allons le publier dans un instant.',
    'comment_update_success' => 'Votre modification de commentaire a été envoyée.',
    'posting_delay' => 'Nous n\'autorisons qu\'un commentaire toutes les deux minutes. Merci de patienter quelques instants avant d\'envoyer le commentaire.',

];