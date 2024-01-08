##
## Installation and update
## -------
.PHONY: install
install: ## Install the project
	composer install
	make cc
	echo "Install complete !"
