import '../styles/frontend.scss';

document.addEventListener('DOMContentLoaded', () => {
	document.body.addEventListener('click', (event) => {
		const target = event.target;
		if (target instanceof HTMLElement && target.closest('.aps-card__cta')) {
			target.setAttribute('data-aps-clicked', '1');
		}
	});
});
