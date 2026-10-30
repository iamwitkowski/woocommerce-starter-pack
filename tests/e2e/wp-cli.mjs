import { execFileSync } from 'node:child_process';

const LOG = 'wp-content/debug.log';

// The test environment has its own containers and database (see .wp-env.test.json).
const CONFIG = process.env.WP_ENV_CONFIG || '.wp-env.test.json';

/**
 * Runs a shell command in the wp-env CLI container and returns trimmed stdout.
 *
 * @param {string} command Shell command.
 * @return {string} Output.
 */
export function wpCli( command ) {
	return execFileSync(
		'npx',
		[ 'wp-env', 'run', 'cli', `--config=${ CONFIG }`, 'sh', '-c', command ],
		{
			encoding: 'utf8',
			stdio: [ 'ignore', 'pipe', 'ignore' ],
		}
	).trim();
}

export function clearPhpLog() {
	wpCli( `: > ${ LOG }` );
}

export function readPhpLog() {
	return wpCli( `cat ${ LOG } 2>/dev/null || true` );
}

/**
 * Applies a theme style variation as the user's global styles ("default" resets them).
 *
 * @param {string} name Variation slug, title or "default".
 */
export function applyVariation( name ) {
	wpCli(
		`wp --user=1 eval-file wp-content/themes/starter-pack/tests/e2e/bin/apply-style-variation.php ${ name }`
	);
}
