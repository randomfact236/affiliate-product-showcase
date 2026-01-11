const { spawnSync } = require('child_process');

// `npm install --package-lock-only` can still run lifecycle scripts.
// In that mode there are no deps installed, so running build is expected to fail.
const isPackageLockOnly = process.env.npm_config_package_lock_only === 'true';
const isDryRun = process.env.npm_config_dry_run === 'true';
const shouldBuild =
	process.env.APS_PREPARE_BUILD === 'true' || process.env.APS_PREPARE_BUILD === '1';

if (isPackageLockOnly || isDryRun) {
	process.exit(0);
}

// Avoid surprising installs: only build during prepare when explicitly requested.
// Enable with `APS_PREPARE_BUILD=1 npm install`.
if (!shouldBuild) {
	process.exit(0);
}

const npmCmd = process.platform === 'win32' ? 'npm.cmd' : 'npm';
const result = spawnSync(npmCmd, ['run', 'build'], { stdio: 'inherit' });

process.exit(typeof result.status === 'number' ? result.status : 1);
