<?php

return [
    'http' => [
        '401' => 'Accès refusé : utilisateur non authentifié.',
        '500' => [
            'general_error' => 'Cette manipulation a généré une erreur. Nos allons veiller à ce que ce problème soit résolu dès que possible.',
            'general_retrieval_error' => 'La ressource demandée est introuvable.',
            'user_not_found' => 'L\'utilisateur sous ce nom est introuvable.',
            'group_not_found' => 'Le groupe sous ce nom est introuvable.',
        ],
        '422' => [
            'oauth_email_unverif' => 'L\'adresse e-mail utilisée pour la connexion n\'est pas vérifiée. Nous ne pouvons donc pas créer un compte sur cette plateforme. La plateforme nécessite une adresse e-mail vérifiée pour pouvoir vous contacter si nécessaire.',
        ],
        '403' => 'L\'accès à cette ressource est interdit.',
        '404' => 'Cette ressource est introuvable. Toutes nos excuses.',
        '405' => 'La plateforme ne peut pas traiter une requête utilisant cette méthode.',
        '419' => 'Votre session à expiré. Merci de vous connecter à nouveau.',
        '429' => 'Le serveur est actuellement surchargé, merci de renouveler votre demande dans quelques secondes.',
        '503' => 'La plateforme n\'est pas disponible. Une opération de maintenance est en cours.',
        'oauth_email' => 'L\'adresse e-mail associée au compte utilisé pour cette connexion existe déjà.',
    ],
    'form' => [
        'identical_passwords' => 'Le mot de passe saisi est identique au mot de passe précédent. Merci de saisir un nouveau mot de passe.',
        'wrong_password' => 'Le mot de passe saisi ne correspond pas au mot de passe enregistré précédemment.',
    ],

];