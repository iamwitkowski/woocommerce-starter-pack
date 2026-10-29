import { execFileSync } from 'node:child_process';

const LOG = 'wp-content/debug.log';

/**
 * Runs a shell command in the wp-env CLI container and returns trimmed stdout.
 *
 * @param {string} command Shell command.
 * @return {string} Output.
 */
export function wpCli( command ) {
	return execFileSync(
		'npx',
		[ 'wp-env', 'run', 'cli', 'sh', '-c', command ],
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
