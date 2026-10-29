import { clearPhpLog } from './wp-cli.mjs';

export default function globalSetup() {
	clearPhpLog();
}
