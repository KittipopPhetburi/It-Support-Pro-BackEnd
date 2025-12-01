import './bootstrap';
import api, { notifications } from './api';

// Example quick usage: fetch notifications and log them
// You can remove these example lines and call `api` / `notifications` from your components.
async function initExample() {
	try {
		const list = await notifications.list();
		console.log('Notifications:', list);
	} catch (err) {
		console.error('Failed to load notifications', err);
	}
}

initExample();
