# CONF'UP

Projet IPSSI

## Installation

1) git clone https://github.com/alexisljn/conf-up.git
2) docker-compose up -d
3) docker-compose exec web php bin/console d:s:u --force
4) docker-compose exec web php bin/console doctrine:fixtures:load
