DB_BACKUP ?= ./scripts/db-backup.sh
DB_RESTORE ?= ./scripts/db-restore.sh
DB_SEED ?= ./scripts/db-seed.sh
WP_PLUGIN ?= ./scripts/wp-plugin.sh
WP_THEME ?= ./scripts/wp-theme.sh
INIT ?= ./scripts/init.sh

.PHONY: db-backup db-restore db-seed

db-backup:
	@echo "Running DB backup..."
	@$(DB_BACKUP) $(OUT)

db-restore:
	@echo "Running DB restore..."
	@$(DB_RESTORE) $(FILE)

db-seed:
	@echo "Running DB seed..."
	@$(DB_SEED)

.PHONY: wp-plugin wp-theme init

wp-plugin:
	@echo "wp-plugin: use 'make wp-plugin PLUGIN=slug' or 'make wp-plugin ACTION=install PLUGIN=slug'"
	@${WP_PLUGIN} ${ACTION:-install} ${PLUGIN}

wp-theme:
	@echo "wp-theme: use 'make wp-theme THEME=slug' or 'make wp-theme ACTION=install THEME=slug'"
	@${WP_THEME} ${ACTION:-install} ${THEME}

init:
	@echo "Running init helper..."
	@${INIT} ${ARGS}
