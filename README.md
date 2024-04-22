# POC - Multi Tenant

## Statut

- La route `/register` permet de créer un utilisateur
  - On peut le mettre "CLIENT_ADMIN" en cochant la checkbox
  - A la création de l'utilisateur, lancer la commande `app:database:create {id}` permet de lui créer : 
    - Son user psql
    - Sa db psql
    - Ses views dans la db common
    - Ses tables dans sa db

- La route `/user` permet à l'utilisateur connecté de voir ses identifiants psql

- La route `/api/login_check` permet de récupérer son token JWT
```bash
curl -k -X POST \
  -H "Content-Type: application/json" https://localhost/api/login_check \
  -d '{"username":"mehdi@les-tilleuls.coop","password":"123456"}' \
```

- Un listener `src/Listener/MigrationCommandListener` permet de jouer la commande `app:database:foreigns` qui va relancer la création des views / tables pour chaque utilisateur quand une migration est réussie

- Un subscriber `src/Subscriber/APIRequestSubscriber` devrait permettre de changer la connection doctrine vers la DB de l'utilisateur auteur de la requête
  - !!! Non testé et je n'ai pas trouvé comment dire à API-P d'utiliser la connection "clients" et pas la connection "default"
  - Actuellement APIP joue les requêtes depuis "default" donc même si ce subscriber fonctionne, le résultat se fera sur la db main

## SQL
Concernant la partie SQL, après recherche il devrait être possible de faire le système suivant : 
1. Supprimer les attributs "owner" de toutes les entités Symfony
2. Demander à PSQL d'automatiquement créer la colonne "owner_id" pour chaque table créé 

Non testé mais [ce code](https://gist.github.com/Checksum/5942ad6a38e75d71e0a9c0912ac83601) devrait le permettre

3. Demander à PSQL d'automatiquement UPDATE l'entité persisté avec son bon owner_id

Non testé mais le trigger ressemblerait à priori à quelque chose comme :

(A créer pour chaque table dans les db clients)
```sql
CREATE TRIGGER update_machine_owner_id 
AFTER INSERT ON client_machines
AS
BEGIN
SET owner_id = id_owner_en_dur
WHERE id IN (SELECT DISTINCT id FROM inserted)
END
```
