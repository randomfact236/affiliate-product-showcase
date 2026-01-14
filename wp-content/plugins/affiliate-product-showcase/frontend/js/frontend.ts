import '../styles/frontend.scss';

document.addEventListener('DOMContentLoaded', (): void => {
	document.body.addEventListener('click', (event: MouseEvent): void => {
		const target = event.target as HTMLElement;
		if (target instanceof HTMLElement && target.closest('.aps-card__cta')) {
			target.setAttribute('data-aps-clicked', '1');
		}
	});
});
