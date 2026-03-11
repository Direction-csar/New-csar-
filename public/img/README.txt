Image de fond pour la page "Site en maintenance"
===============================================

Placez ici votre image de fond (celle qui remplace le drapeau).

Nom du fichier attendu : maintenance-bg.jpg

Vous pouvez aussi utiliser maintenance-bg.png — dans ce cas, modifiez dans :
  - resources/views/errors/503.blade.php : asset('img/maintenance-bg.png')
  - public/maintenance.html : url("img/maintenance-bg.png")

La page applique automatiquement un overlay sombre (72 %) pour que le texte
"Site en maintenance" reste lisible et que l’ambiance soit clairement "maintenance".
