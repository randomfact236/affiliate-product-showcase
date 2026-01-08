DB_BACKUP ?= ./scripts/db-backup.sh
DB_RESTORE ?= ./scripts/db-restore.sh
DB_SEED ?= ./scripts/db-seed.sh

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
