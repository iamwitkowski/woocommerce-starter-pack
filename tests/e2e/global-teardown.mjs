import { readPhpLog } from './wp-cli.mjs';

// Any PHP notice, warning or error logged during the run fails the suite.
export default function globalTeardown() {
	const log = readPhpLog();
	if ( log ) {
		throw new Error(
			`PHP debug.log is not empty after the E2E run:\n${ log }`
		);
	}
}
