const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
const mobileSiteNav = document.querySelector('[data-mobile-site-nav]');

if (mobileMenuToggle && mobileSiteNav) {
	const setMobileMenuOpen = (isOpen, restoreFocus = false) => {
		mobileSiteNav.hidden = !isOpen;
		mobileMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		mobileMenuToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
		if (restoreFocus) mobileMenuToggle.focus();
	};

	mobileMenuToggle.addEventListener('click', () => {
		setMobileMenuOpen(mobileSiteNav.hidden);
	});

	mobileSiteNav.querySelectorAll('a, button').forEach((link) => {
		link.addEventListener('click', () => setMobileMenuOpen(false));
	});

	document.addEventListener('pointerdown', (event) => {
		if (!event.target.closest('[data-mobile-site-nav], [data-mobile-menu-toggle]')) setMobileMenuOpen(false);
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !mobileSiteNav.hidden) setMobileMenuOpen(false, true);
	});

	window.matchMedia('(min-width: 768px)').addEventListener('change', (event) => {
		if (event.matches) setMobileMenuOpen(false);
	});
}

const adminMenuToggle = document.querySelector('[data-admin-menu-toggle]');
const adminNavigation = document.querySelector('[data-admin-navigation]');

if (adminMenuToggle && adminNavigation) {
	const closeAdminMenu = () => {
		adminNavigation.classList.remove('is-open');
		adminMenuToggle.setAttribute('aria-expanded', 'false');
		adminMenuToggle.setAttribute('aria-label', 'Abrir menú de administración');
	};

	adminMenuToggle.addEventListener('click', () => {
		const isOpen = adminNavigation.classList.toggle('is-open');
		adminMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		adminMenuToggle.setAttribute('aria-label', isOpen ? 'Cerrar menú de administración' : 'Abrir menú de administración');
	});

	adminNavigation.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeAdminMenu));
	document.addEventListener('click', (event) => {
		if (!event.target.closest('.admin-header-actions')) closeAdminMenu();
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') closeAdminMenu();
	});
}

document.querySelectorAll('[data-suggestions]').forEach((input) => {
	const menu = input.parentElement.querySelector('[data-suggestion-menu]');
	const suggestions = JSON.parse(input.dataset.suggestions || '[]');

	const closeMenu = () => {
		menu.classList.remove('is-visible');
		menu.innerHTML = '';
	};

	input.addEventListener('input', () => {
		const value = input.value.trim().toLowerCase();
		const matches = suggestions
			.filter((suggestion) => suggestion.toLowerCase().includes(value))
			.slice(0, 6);

		if (!matches.length || !value) {
			closeMenu();
			return;
		}

		menu.innerHTML = matches.map((suggestion) => `<button type="button" data-suggestion-value="${suggestion.replace(/"/g, '&quot;')}">${suggestion}</button>`).join('');
		menu.classList.add('is-visible');
		menu.querySelectorAll('[data-suggestion-value]').forEach((button) => {
			button.addEventListener('mousedown', (event) => event.preventDefault());
			button.addEventListener('click', () => {
				input.value = button.dataset.suggestionValue;
				closeMenu();
			});
		});
	});

	input.addEventListener('blur', () => window.setTimeout(closeMenu, 120));
});

document.querySelectorAll('[data-auto-submit-search]').forEach((input) => {
	const form = input.closest('form');
	const initialResults = document.querySelector('[data-order-results]');
	let searchTimeout;
	let searchController;

	if (!form || !initialResults) return;

	const updateResults = async () => {
		const pageUrl = new URL(form.action, window.location.href);
		const query = new URLSearchParams(new FormData(form));
		query.delete('page');
		if (!query.get('search')?.trim()) query.delete('search');
		pageUrl.search = query.toString();

		const requestUrl = new URL(pageUrl);
		searchController?.abort();
		searchController = new AbortController();
		const activeResults = document.querySelector('[data-order-results]');
		activeResults?.classList.add('is-loading');
		activeResults?.setAttribute('aria-busy', 'true');

		try {
			const response = await fetch(requestUrl, {
				headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
				signal: searchController.signal,
			});
			if (!response.ok) throw new Error('No se pudieron actualizar los pedidos.');

			const payload = await response.json();
			const parsedResponse = new DOMParser().parseFromString(payload.html, 'text/html');
			const nextResults = parsedResponse.querySelector('[data-order-results]');
			const currentResults = document.querySelector('[data-order-results]');
			if (!nextResults || !currentResults) throw new Error('La respuesta de pedidos no es válida.');

			currentResults.replaceWith(nextResults);
			const total = document.querySelector('[data-orders-total]');
			if (total) total.textContent = payload.total;
			window.history.replaceState({}, '', `${pageUrl.pathname}${pageUrl.search}${pageUrl.hash}`);
		} catch (error) {
			if (error.name === 'AbortError') return;
			window.location.assign(`${pageUrl.pathname}${pageUrl.search}${pageUrl.hash}`);
		}
	};

	input.addEventListener('input', () => {
		window.clearTimeout(searchTimeout);
		searchTimeout = window.setTimeout(() => form.requestSubmit(), 200);
	});

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		window.clearTimeout(searchTimeout);
		updateResults();
	});
});

const categoryModal = document.querySelector('[data-category-modal]');
const categoryForm = document.querySelector('[data-category-form]');
const categoryInput = document.querySelector('input[name="category"]');
const categoryQuickList = document.querySelector('[data-category-quick-list]');
const categoryTree = document.querySelector('[data-category-tree]');
const adminCategoryParent = document.querySelector('[data-category-parent]');
const adminCategorySegment = document.querySelector('[data-category-segment]');
const adminCategoryPathPreview = document.querySelector('[data-category-path-preview]');

const updateAdminCategoryPathPreview = () => {
	if (!adminCategoryPathPreview || !adminCategorySegment) return;

	const segment = adminCategorySegment.value.trim();
	const parent = adminCategoryParent?.value.trim() || '';
	adminCategoryPathPreview.textContent = segment ? (parent ? `${parent} > ${segment}` : segment) : 'Nueva categoría';
};

adminCategoryParent?.addEventListener('change', updateAdminCategoryPathPreview);
adminCategorySegment?.addEventListener('input', updateAdminCategoryPathPreview);
updateAdminCategoryPathPreview();

document.querySelectorAll('[data-category-branch-toggle]').forEach((toggle) => {
	const branchId = toggle.getAttribute('aria-controls');
	const branch = branchId ? document.getElementById(branchId) : null;

	if (!branch) return;

	toggle.addEventListener('click', () => {
		const expanded = toggle.getAttribute('aria-expanded') !== 'true';
		toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		branch.hidden = !expanded;
		toggle.closest('.admin-category-tree-node')?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
	});
});

const updateCategorySelection = () => {
	categoryTree?.querySelectorAll('[data-category-choice]').forEach((choice) => {
		const isSelected = choice.dataset.categoryChoice === categoryInput?.value.trim();
		choice.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
		choice.classList.toggle('is-selected', isSelected);
	});
};

const renderCategoryTree = () => {
	if (!categoryQuickList || !categoryTree || !categoryInput) return;

	const root = { children: new Map() };
	categoryQuickList.querySelectorAll('[data-category-choice]').forEach((choice) => {
		const segments = choice.dataset.categoryChoice.split('>').map((segment) => segment.trim()).filter(Boolean);
		let node = root;
		let path = '';

		segments.forEach((name, index) => {
			path = path ? `${path} > ${name}` : name;
			if (!node.children.has(name)) node.children.set(name, { name, path, children: new Map() });
			node = node.children.get(name);
			if (index === segments.length - 1) node.selectable = true;
		});
	});

	const appendNodes = (parent, nodes) => {
		[...nodes].sort((left, right) => left.name.localeCompare(right.name, 'es')).forEach((node) => {
			const item = document.createElement('li');
			item.className = 'category-tree-item';
			const row = document.createElement('div');
			row.className = 'category-tree-row';

			const select = document.createElement('button');
			select.type = 'button';
			select.className = 'category-tree-select';
			select.dataset.categoryChoice = node.path;
			select.textContent = node.name;
			select.setAttribute('aria-pressed', categoryInput.value === node.path ? 'true' : 'false');
			row.append(select);

			if (node.children.size > 0) {
				item.classList.add('has-children');
				const toggle = document.createElement('button');
				toggle.type = 'button';
				toggle.className = 'category-tree-toggle';
				toggle.dataset.categoryTreeToggle = '';
				toggle.setAttribute('aria-expanded', 'false');
				toggle.setAttribute('aria-label', `Mostrar subcategorías de ${node.name}`);
				toggle.textContent = '›';
				row.append(toggle);

				const submenu = document.createElement('ul');
				submenu.className = 'category-tree-submenu';
				appendNodes(submenu, node.children.values());
				item.append(row, submenu);

				const syncExpanded = (expanded) => {
					if (!item.classList.contains('is-open')) toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
				};
				item.addEventListener('mouseenter', () => syncExpanded(true));
				item.addEventListener('mouseleave', () => syncExpanded(item.contains(document.activeElement)));
				item.addEventListener('focusin', () => syncExpanded(true));
				item.addEventListener('focusout', (event) => {
					if (!item.contains(event.relatedTarget)) syncExpanded(false);
				});
			} else {
				item.append(row);
			}

			parent.append(item);
		});
	};

	appendNodes(categoryTree, root.children.values());
	updateCategorySelection();
};

renderCategoryTree();
categoryInput?.addEventListener('input', updateCategorySelection);

categoryTree?.addEventListener('click', (event) => {
	const toggle = event.target.closest('[data-category-tree-toggle]');
	if (toggle) {
		const item = toggle.closest('.category-tree-item');
		const isOpen = item.classList.toggle('is-open');
		const isExpanded = isOpen || item.matches(':hover') || item.contains(document.activeElement);
		toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
		return;
	}

	const choice = event.target.closest('[data-category-choice]');
	if (!choice) return;

	categoryInput.value = choice.dataset.categoryChoice;
	categoryInput.dispatchEvent(new Event('input', { bubbles: true }));
	categoryTree.querySelectorAll('.category-tree-item.is-open').forEach((item) => {
		item.classList.remove('is-open');
		item.querySelector('[data-category-tree-toggle]')?.setAttribute('aria-expanded', 'false');
	});
});

if (categoryModal && categoryForm && categoryInput) {
	const categoryName = categoryForm.querySelector('input[name="name"]');
	const categoryError = categoryForm.querySelector('[data-category-error]');
	const closeCategoryModal = () => {
		categoryModal.classList.remove('is-visible');
		categoryModal.setAttribute('aria-hidden', 'true');
		categoryForm.reset();
		categoryError.textContent = '';
	};

	document.querySelector('[data-open-category-modal]')?.addEventListener('click', () => {
		categoryModal.classList.add('is-visible');
		categoryModal.setAttribute('aria-hidden', 'false');
		window.setTimeout(() => categoryName.focus(), 100);
	});
	document.querySelector('[data-close-category-modal]')?.addEventListener('click', closeCategoryModal);
	categoryModal.addEventListener('click', (event) => {
		if (event.target === categoryModal) closeCategoryModal();
	});

	categoryForm.addEventListener('submit', async (event) => {
		event.preventDefault();
		categoryError.textContent = '';
		const button = categoryForm.querySelector('button[type="submit"]');
		button.disabled = true;

		try {
			const response = await fetch(categoryForm.action || '/admin/categorias', {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
				body: new FormData(categoryForm),
			});
			const payload = await response.json();
			if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'No se pudo guardar la categoría.');
			categoryInput.value = payload.category;
			categoryInput.dispatchEvent(new Event('input', { bubbles: true }));
			if (categoryQuickList && ![...categoryQuickList.querySelectorAll('[data-category-choice]')].some((choice) => choice.dataset.categoryChoice === payload.category)) {
				const choice = document.createElement('button');
				choice.type = 'button';
				choice.dataset.categoryChoice = payload.category;
				choice.textContent = payload.category;
				categoryQuickList.appendChild(choice);
				renderCategoryTree();
			}
			closeCategoryModal();
		} catch (error) {
			categoryError.textContent = error.message;
		} finally {
			if (!button.textContent.includes('Agotado')) button.disabled = false;
		}
	});
}

const countdowns = document.querySelectorAll('[data-countdown]');
const updateCountdowns = () => {
	countdowns.forEach((counter) => {
		const remaining = new Date(counter.dataset.countdown).getTime() - Date.now();
		const display = counter.querySelector('strong');
		if (remaining <= 0) {
			counter.classList.add('is-expired');
			display.textContent = 'Oferta finalizada';
			return;
		}

		const hours = Math.floor(remaining / 3600000);
		const minutes = Math.floor((remaining % 3600000) / 60000);
		const seconds = Math.floor((remaining % 60000) / 1000);
		display.querySelector('[data-hours]').textContent = String(hours).padStart(2, '0');
		display.querySelector('[data-minutes]').textContent = String(minutes).padStart(2, '0');
		display.querySelector('[data-seconds]').textContent = String(seconds).padStart(2, '0');
	});
};

if (countdowns.length) {
	updateCountdowns();
	window.setInterval(updateCountdowns, 1000);
}

const manualOfferToggle = document.querySelector('[data-manual-offer-toggle]');
const manualOfferFields = document.querySelector('[data-manual-offer-fields]');
const basePriceInput = document.querySelector('[data-base-price]');
const salePriceInput = document.querySelector('[data-sale-price]');
const profitPercentageInput = document.querySelector('[data-profit-percentage]');
const includesIgvInput = document.querySelector('[data-include-igv]');
const includesSurchargeInput = document.querySelector('[data-include-surcharge]');
const offerPercentageInput = document.querySelector('[data-offer-percentage]');
const finalPriceOutput = document.querySelector('[data-final-price]');
const finalPriceLabel = document.querySelector('[data-final-price-label]');

const formatSoles = (value) => `S/ ${value.toFixed(2).replace('.', ',')}`;

const updateSalePrice = () => {
	if (!basePriceInput || !salePriceInput || !profitPercentageInput) return;
	const basePrice = Number.parseFloat(basePriceInput.value) || 0;
	const profit = Number.parseFloat(profitPercentageInput.value) || 0;
	const priceWithMargin = basePrice + (basePrice * (profit / 100));
	const includesIgv = Boolean(includesIgvInput?.checked);
	const includesSurcharge = Boolean(includesSurchargeInput?.checked);
	const afterIgv = includesIgv ? priceWithMargin * 1.18 : priceWithMargin;
	const surchargeAmount = includesSurcharge ? afterIgv * 0.01 : 0;
	const salePrice = afterIgv + surchargeAmount;
	salePriceInput.value = (salePrice + 0.0000001).toFixed(2);
};

const updateFinalPricePreview = () => {
	if (!salePriceInput || !finalPriceOutput) return;
	updateSalePrice();
	const salePrice = Number.parseFloat(salePriceInput.value) || 0;
	const manualOfferSelected = Boolean(manualOfferToggle?.checked);
	const discount = manualOfferSelected ? Number.parseFloat(offerPercentageInput?.value) || 0 : 0;
	const finalPrice = Number((salePrice * (1 - discount / 100) + 0.0000001).toFixed(2));
	const finalLabel = manualOfferSelected ? 'Precio final con promoción:' : 'Precio de venta antes de promoción:';
	finalPriceOutput.textContent = formatSoles(finalPrice);
	if (finalPriceLabel) finalPriceLabel.textContent = finalLabel;
};

basePriceInput?.addEventListener('input', updateFinalPricePreview);
profitPercentageInput?.addEventListener('change', updateFinalPricePreview);
includesIgvInput?.addEventListener('change', updateFinalPricePreview);
includesSurchargeInput?.addEventListener('change', updateFinalPricePreview);
salePriceInput?.addEventListener('input', updateFinalPricePreview);
offerPercentageInput?.addEventListener('change', updateFinalPricePreview);

if (manualOfferToggle && manualOfferFields) {
	const syncManualOfferFields = () => {
		manualOfferFields.classList.toggle('is-visible', manualOfferToggle.checked);
	};

	manualOfferToggle.addEventListener('change', () => {
		syncManualOfferFields();
		updateFinalPricePreview();
	});
	syncManualOfferFields();
}

updateFinalPricePreview();

const dropzone = document.querySelector('[data-dropzone]');
const imageInput = document.querySelector('[data-image-input]');
const dropzoneFile = document.querySelector('[data-dropzone-file]');
const newImageGallery = document.querySelector('[data-new-image-gallery]');

if (dropzone && imageInput && dropzoneFile && newImageGallery) {
	let selectedFiles = [];
	let previewUrls = [];
	const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
	const fileKey = (file) => `${file.name}-${file.size}-${file.lastModified}`;

	const showFiles = (files) => {
		const invalidFile = files.find((file) => !validTypes.includes(file.type) || file.size > 4 * 1024 * 1024);
		if (invalidFile) {
			dropzoneFile.textContent = 'Usa JPG, PNG o WEBP de máximo 4 MB.';
			dropzone.classList.add('has-error');
			return;
		}
		dropzone.classList.remove('has-error');
		previewUrls.forEach((url) => URL.revokeObjectURL(url));
		previewUrls = files.map((file) => URL.createObjectURL(file));
		newImageGallery.replaceChildren();
		files.forEach((file, index) => {
			const figure = document.createElement('figure');
			const image = document.createElement('img');
			const caption = document.createElement('figcaption');
			image.src = previewUrls[index];
			image.alt = `Vista previa de ${file.name}`;
			caption.textContent = file.name;
			figure.append(image, caption);
			newImageGallery.appendChild(figure);
		});
		dropzoneFile.textContent = files.length === 1
			? `✓ ${files[0].name}`
			: `✓ ${files.length} imágenes seleccionadas: ${files.map((file) => file.name).join(', ')}`;
		dropzone.classList.add('has-file');
	};

	const addFiles = (files) => {
		const newFiles = files.filter((file) => validTypes.includes(file.type) && file.size <= 4 * 1024 * 1024);
		selectedFiles = [...selectedFiles, ...newFiles.filter((file) => !selectedFiles.some((selectedFile) => fileKey(selectedFile) === fileKey(file)))];
		const transfer = new DataTransfer();
		selectedFiles.forEach((file) => transfer.items.add(file));
		imageInput.files = transfer.files;
		showFiles(selectedFiles);
	};

	imageInput.addEventListener('change', () => addFiles([...imageInput.files]));
	['dragenter', 'dragover'].forEach((eventName) => dropzone.addEventListener(eventName, (event) => {
		event.preventDefault();
		dropzone.classList.add('is-dragging');
	}));
	['dragleave', 'drop'].forEach((eventName) => dropzone.addEventListener(eventName, (event) => {
		event.preventDefault();
		dropzone.classList.remove('is-dragging');
	}));
	dropzone.addEventListener('drop', (event) => {
		const files = [...event.dataTransfer.files];
		if (!files.length) return;
		addFiles(files);
	});
}

document.querySelectorAll('.product-card').forEach((card) => {
	card.addEventListener('pointermove', (event) => {
		const bounds = card.getBoundingClientRect();
		card.style.setProperty('--magic-x', `${event.clientX - bounds.left}px`);
		card.style.setProperty('--magic-y', `${event.clientY - bounds.top}px`);
	});
});

const heroParticleField = document.querySelector('.hero-shell');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const supportsFinePointer = window.matchMedia('(pointer: fine)').matches;

if (heroParticleField && !prefersReducedMotion && supportsFinePointer) {
	const trailConfigs = [{ count: 30, offsetX: 0, offsetY: 0 }];
	const trails = trailConfigs.map((config, trailIndex) => {
		const element = document.createElement('div');
		element.className = `cursor-particle-trail cursor-particle-trail-${trailIndex + 1}`;
		element.setAttribute('aria-hidden', 'true');
		const particles = Array.from({ length: config.count }, (_, index) => {
			const particle = document.createElement('span');
			particle.style.setProperty('--particle-index', index);
			particle.style.opacity = String(Math.max(.12, .92 - (index * .027)));
			element.appendChild(particle);
			return { element: particle, x: 0, y: 0 };
		});
		heroParticleField.appendChild(element);
		return { element, particles, offsetX: config.offsetX, offsetY: config.offsetY };
	});
	const target = { x: 0, y: 0 };
	let lastSparkPoint = null;
	let sparkIndex = 0;
	let animationFrame = 0;
	let isActive = false;
	const emitDistanceSparks = (x, y) => {
		if (!lastSparkPoint) {
			lastSparkPoint = { x, y };
			return;
		}

		const deltaX = x - lastSparkPoint.x;
		const deltaY = y - lastSparkPoint.y;
		const distance = Math.hypot(deltaX, deltaY);
		const sparkCount = Math.min(Math.floor(distance / 14), 5);
		if (sparkCount < 1) return;

		for (let index = 1; index <= sparkCount; index++) {
			const progress = index / sparkCount;
			const sparkle = document.createElement('span');
			sparkle.className = `cursor-distance-spark${sparkIndex++ % 2 ? ' is-violet' : ''}`;
			sparkle.style.left = `${lastSparkPoint.x + deltaX * progress}px`;
			sparkle.style.top = `${lastSparkPoint.y + deltaY * progress}px`;
			sparkle.style.setProperty('--spark-drift-x', `${(Math.random() - .5) * 28}px`);
			sparkle.style.setProperty('--spark-drift-y', `${(Math.random() - .5) * 28}px`);
			heroParticleField.appendChild(sparkle);
			window.setTimeout(() => sparkle.remove(), 720);
		}

		lastSparkPoint = { x, y };
	};

	const animateTrail = () => {
		trails.forEach((trail) => {
			trail.particles.forEach((particle, index) => {
				const leader = index === 0
					? { x: target.x + trail.offsetX, y: target.y + trail.offsetY }
					: trail.particles[index - 1];
				const easing = index === 0 ? .28 : .18;
				particle.x += (leader.x - particle.x) * easing;
				particle.y += (leader.y - particle.y) * easing;
				particle.element.style.transform = `translate3d(${particle.x}px, ${particle.y}px, 0)`;
			});
		});

		if (isActive) animationFrame = window.requestAnimationFrame(animateTrail);
	};

	heroParticleField.addEventListener('pointerenter', (event) => {
		if (event.pointerType === 'touch') return;
		const bounds = heroParticleField.getBoundingClientRect();
		target.x = event.clientX - bounds.left;
		target.y = event.clientY - bounds.top;
		lastSparkPoint = { x: target.x, y: target.y };
		isActive = true;
		trails.forEach((trail) => trail.element.classList.add('is-visible'));
		if (!animationFrame) animationFrame = window.requestAnimationFrame(animateTrail);
	});

	heroParticleField.addEventListener('pointermove', (event) => {
		if (event.pointerType === 'touch') return;
		const bounds = heroParticleField.getBoundingClientRect();
		target.x = event.clientX - bounds.left;
		target.y = event.clientY - bounds.top;
		emitDistanceSparks(target.x, target.y);
	});

	heroParticleField.addEventListener('pointerleave', () => {
		isActive = false;
		lastSparkPoint = null;
		trails.forEach((trail) => trail.element.classList.remove('is-visible'));
		window.cancelAnimationFrame(animationFrame);
		animationFrame = 0;
	});
}

document.querySelectorAll('[data-product-carousel]').forEach((carousel) => {
	const images = [...carousel.querySelectorAll('.product-image')];
	if (images.length < 2) return;
	let activeIndex = 0;

	window.setInterval(() => {
		images[activeIndex].classList.remove('is-active');
		activeIndex = (activeIndex + 1) % images.length;
		images[activeIndex].classList.add('is-active');
	}, 3200);
});

const categoryFilterShell = document.querySelector('[data-category-filter-shell]');

if (categoryFilterShell) {
	const cards = [...document.querySelectorAll('[data-product-card]')];
	const emptyState = document.querySelector('[data-category-empty]');
	const resultCount = categoryFilterShell.querySelector('[data-category-result-count]');
	const productSearchInput = categoryFilterShell.querySelector('[data-product-search]');
	const productSearchHint = categoryFilterShell.querySelector('[data-product-search-hint]');
	const emptyTitle = emptyState?.querySelector('[data-product-empty-title]');
	const emptyCopy = emptyState?.querySelector('[data-product-empty-copy]');
	const emptyReset = emptyState?.querySelector('[data-product-empty-reset]');
	const normalizeCategory = (category) => category.trim().toLocaleLowerCase();
	const normalizeSearch = (value) => value.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.toLocaleLowerCase()
		.replace(/[^a-z0-9\s]/g, ' ')
		.trim()
		.replace(/\s+/g, ' ');
	const searchableProducts = new Map(cards.map((card) => [card, {
		text: normalizeSearch(card.dataset.productSearchText || ''),
		words: [...new Set(normalizeSearch(card.dataset.productSearchSimilar || '').split(' '))].filter((word) => word.length >= 4),
	}]));
	const categoryRoot = { name: '', path: '', children: new Map(), exactCount: 0, count: 0, hasRoute: false };
	const categoryRoutes = [...categoryFilterShell.querySelectorAll('[data-category-route]')].map((route) => ({
		path: route.dataset.categoryRoute || '',
		count: Number(route.dataset.categoryCount || 0),
	}));
	const breadcrumbs = categoryFilterShell.querySelector('[data-category-breadcrumbs]');
	const explorer = categoryFilterShell.querySelector('[data-category-explorer]');
	const levelKicker = categoryFilterShell.querySelector('[data-category-level-kicker]');
	const levelTitle = categoryFilterShell.querySelector('[data-category-level-title]');
	const levelDescription = categoryFilterShell.querySelector('[data-category-level-description]');
	const levelCount = categoryFilterShell.querySelector('[data-category-level-count]');
	let currentPath = '';
	let selectedCategory = '__offers__';
	let searchTerms = [];
	let activeProductMatcher = () => true;
	let activeProductLabel = (count) => count === 1 ? 'producto' : 'productos';

	categoryRoutes.forEach(({ path, count }) => {
		let node = categoryRoot;
		node.count += count;

		path.split(' > ').filter(Boolean).forEach((name) => {
			let child = node.children.get(name);

			if (!child) {
				child = { name, path: node.path ? `${node.path} > ${name}` : name, children: new Map(), exactCount: 0, count: 0, hasRoute: false };
				node.children.set(name, child);
			}

			child.count += count;
			node = child;
		});

		if (node !== categoryRoot) {
			node.exactCount += count;
			node.hasRoute = true;
		}
	});

	const getNode = (path) => {
		if (!path) return categoryRoot;

		return path.split(' > ').filter(Boolean).reduce((node, name) => node?.children.get(name), categoryRoot);
	};

	const appendBreadcrumb = (label, path, current = false) => {
		const item = document.createElement(current ? 'span' : 'button');
		item.className = current ? 'category-breadcrumb is-current' : 'category-breadcrumb';
		item.textContent = label;

		if (current) {
			item.setAttribute('aria-current', 'page');
		} else {
			item.type = 'button';
			item.dataset.categoryOpenPath = path;
		}

		breadcrumbs?.append(item);
	};

	const appendBreadcrumbSeparator = () => {
		const separator = document.createElement('span');
		separator.className = 'category-breadcrumb-separator';
		separator.setAttribute('aria-hidden', 'true');
		separator.textContent = '›';
		breadcrumbs?.append(separator);
	};

	const createExplorerCard = (node) => {
		const item = document.createElement('article');
		item.className = 'category-explorer-item';

		const button = document.createElement('button');
		button.type = 'button';
		button.className = `category-explorer-card${selectedCategory === node.path ? ' is-selected' : ''}`;
		const productLabel = node.count === 1 ? 'producto' : 'productos';
		button.setAttribute('aria-label', node.children.size > 0 ? `Explorar ${node.name}, ${node.children.size} subcategorías` : `Ver ${node.count} ${productLabel} de ${node.name}`);

		if (node.children.size > 0) {
			button.dataset.categoryOpenPath = node.path;
		} else {
			button.dataset.categoryFilter = node.path;
			button.setAttribute('aria-pressed', selectedCategory === node.path ? 'true' : 'false');
		}

		const icon = document.createElement('span');
		icon.className = 'category-explorer-icon';
		icon.setAttribute('aria-hidden', 'true');
		icon.textContent = node.name.trim().split(/\s+/).slice(0, 2).map((word) => word[0]).join('').toLocaleUpperCase();

		const copy = document.createElement('span');
		copy.className = 'category-explorer-copy';
		const title = document.createElement('strong');
		title.textContent = node.name;
		const detail = document.createElement('small');
		detail.textContent = node.children.size > 0
			? `${node.children.size} ${node.children.size === 1 ? 'subcategoría' : 'subcategorías'}`
			: `${node.count} ${node.count === 1 ? 'producto' : 'productos'}`;
		copy.append(title, detail);

		const count = document.createElement('span');
		count.className = 'category-explorer-count';
		count.textContent = String(node.count);

		const arrow = document.createElement('span');
		arrow.className = 'category-explorer-arrow';
		arrow.setAttribute('aria-hidden', 'true');
		arrow.textContent = node.children.size > 0 ? '›' : '↗';
		button.append(icon, copy, count, arrow);
		item.append(button);

		if (node.children.size > 0 && node.exactCount > 0) {
			const exactFilter = document.createElement('button');
			exactFilter.type = 'button';
			exactFilter.className = 'category-explorer-exact-filter';
			exactFilter.dataset.categoryFilter = node.path;
			exactFilter.setAttribute('aria-pressed', selectedCategory === node.path ? 'true' : 'false');
			exactFilter.textContent = `Ver ${node.exactCount} ${node.exactCount === 1 ? 'producto' : 'productos'} de esta categoría`;
			item.append(exactFilter);
		}

		return item;
	};

	const renderExplorer = () => {
		const node = getNode(currentPath) || categoryRoot;
		const children = [...node.children.values()]
			.filter((child) => child.count > 0)
			.sort((left, right) => left.name.localeCompare(right.name, 'es'));

		if (breadcrumbs) {
			breadcrumbs.replaceChildren();
			if (currentPath) {
				appendBreadcrumb('Todas las secciones', '');
				const segments = currentPath.split(' > ').filter(Boolean);
				let path = '';

				segments.forEach((segment, index) => {
					path = path ? `${path} > ${segment}` : segment;
					appendBreadcrumbSeparator();
					appendBreadcrumb(segment, path, index === segments.length - 1);
				});
			} else if (selectedCategory === '__offers__') {
				appendBreadcrumb('Todas las secciones', '');
			} else {
				appendBreadcrumb('Todas las secciones', '', true);
			}
		}

		if (levelKicker) levelKicker.textContent = currentPath ? 'EXPLORA LA SECCIÓN' : 'CATÁLOGO';
		if (levelTitle) levelTitle.textContent = currentPath ? node.name : 'Secciones principales';
		if (levelDescription) levelDescription.textContent = currentPath
			? 'Continúa explorando o selecciona una categoría para ver sus productos.'
			: 'Elige un universo para descubrir sus categorías y subcategorías.';
		if (levelCount) levelCount.textContent = `${children.length} ${children.length === 1 ? 'opción' : 'opciones'}`;
		if (explorer) explorer.replaceChildren(...children.map(createExplorerCard));
	};

	const updateFilterButtons = (category) => {
		categoryFilterShell.querySelectorAll('[data-category-filter]').forEach((filter) => {
			const isActive = normalizeCategory(filter.dataset.categoryFilter || '') === normalizeCategory(category);
			filter.classList.toggle('is-active', isActive);
			filter.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});
	};

	const isSimilarWord = (term, candidate) => {
		const maxDistance = term.length >= 6 ? 2 : 1;
		if (Math.abs(term.length - candidate.length) > maxDistance) return false;

		let previousRow = Array.from({ length: candidate.length + 1 }, (_, index) => index);
		for (let termIndex = 1; termIndex <= term.length; termIndex++) {
			const currentRow = [termIndex];
			let rowMinimum = termIndex;

			for (let candidateIndex = 1; candidateIndex <= candidate.length; candidateIndex++) {
				const cost = term[termIndex - 1] === candidate[candidateIndex - 1] ? 0 : 1;
				const distance = Math.min(
					previousRow[candidateIndex] + 1,
					currentRow[candidateIndex - 1] + 1,
					previousRow[candidateIndex - 1] + cost,
				);
				currentRow[candidateIndex] = distance;
				rowMinimum = Math.min(rowMinimum, distance);
			}

			if (rowMinimum > maxDistance) return false;
			previousRow = currentRow;
		}

		return previousRow[candidate.length] <= maxDistance;
	};

	const renderVisibleProducts = () => {
		let visibleCount = 0;

		cards.forEach((card) => {
			const product = searchableProducts.get(card);
			const matchesSearch = searchTerms.every((term) => product.text.includes(term)
				|| (term.length >= 4 && product.words.some((word) => isSimilarWord(term, word))));
			const isVisible = activeProductMatcher(card) && matchesSearch;
			card.hidden = !isVisible;
			if (isVisible) visibleCount++;
		});

		if (resultCount) {
			const label = searchTerms.length
				? (visibleCount === 1 ? 'coincidencia' : 'coincidencias')
				: activeProductLabel(visibleCount);
			resultCount.textContent = `${visibleCount} ${label}`;
		}
		if (productSearchHint) {
			productSearchHint.textContent = searchTerms.length
				? (visibleCount > 0 ? `${visibleCount} ${visibleCount === 1 ? 'producto encontrado' : 'productos encontrados'}, incluyendo nombres parecidos.` : 'No encontramos productos parecidos. Prueba con otra palabra.')
				: 'Encuentra productos aunque escribas un nombre parecido.';
		}
		if (emptyState) emptyState.hidden = visibleCount !== 0;
		if (emptyTitle) emptyTitle.textContent = searchTerms.length ? 'No encontramos productos parecidos' : 'Este universo aún está expandiéndose';
		if (emptyCopy) emptyCopy.textContent = searchTerms.length ? 'Prueba con otro nombre, categoría o descripción.' : 'No hay productos disponibles en esta categoría por ahora.';
		if (emptyReset) emptyReset.textContent = searchTerms.length ? 'Limpiar búsqueda' : 'Ver ofertas';

		return visibleCount;
	};

	const updateProducts = (matches, label, hash, updateHash) => {
		activeProductMatcher = matches;
		activeProductLabel = label;
		renderVisibleProducts();
		if (updateHash) window.history.replaceState(null, '', hash);
	};

	const applyCategory = (category, updateHash = true) => {
		const normalizedCategory = normalizeCategory(category);
		const selectedNode = getNode(category);

		if (normalizedCategory !== '__offers__' && (!selectedNode || selectedNode.count <= 0)) {
			applyCategory('__offers__', updateHash);
			return;
		}

		selectedCategory = category;
		updateFilterButtons(category);

		if (normalizedCategory === '__offers__') {
			currentPath = '';
			renderExplorer();
			updateProducts(
				(card) => card.dataset.productOffer === 'true',
				(count) => count === 1 ? 'oferta' : 'ofertas',
				`#productos-${encodeURIComponent(category)}`,
				updateHash,
			);
			return;
		}

		currentPath = selectedNode?.children.size > 0 ? category : category.split(' > ').slice(0, -1).join(' > ');
		renderExplorer();
		updateFilterButtons(category);
		updateProducts(
			(card) => normalizeCategory(card.dataset.productCategory || '') === normalizedCategory,
			(count) => count === 1 ? 'producto' : 'productos',
			`#productos-${encodeURIComponent(category)}`,
			updateHash,
		);
	};

	const applyBrowse = (path, updateHash = true) => {
		const browseNode = getNode(path);

		if (path && (!browseNode || browseNode.count <= 0)) {
			applyCategory('__offers__', updateHash);
			return;
		}

		currentPath = path;
		selectedCategory = '';
		renderExplorer();
		updateFilterButtons('');
		const normalizedPath = normalizeCategory(path);
		updateProducts(
			(card) => {
				const productCategory = normalizeCategory(card.dataset.productCategory || '');
				return !normalizedPath || productCategory === normalizedPath || productCategory.startsWith(`${normalizedPath} > `);
			},
			(count) => count === 1 ? 'producto' : 'productos',
			`#categorias-${encodeURIComponent(path)}`,
			updateHash,
		);
	};

	categoryFilterShell.addEventListener('click', (event) => {
		const openButton = event.target.closest('[data-category-open-path]');
		if (openButton && categoryFilterShell.contains(openButton)) {
			applyBrowse(openButton.dataset.categoryOpenPath || '');
			return;
		}

		const filterButton = event.target.closest('[data-category-filter]');
		if (filterButton && categoryFilterShell.contains(filterButton)) {
			applyCategory(filterButton.dataset.categoryFilter || '');
		}
	});

	productSearchInput?.addEventListener('input', () => {
		searchTerms = normalizeSearch(productSearchInput.value).split(' ').filter(Boolean);
		renderVisibleProducts();
	});

	document.querySelector('[data-category-reset]')?.addEventListener('click', () => {
		if (productSearchInput) productSearchInput.value = '';
		searchTerms = [];
		applyCategory('__offers__');
	});

	const hash = window.location.hash;
	if (hash.startsWith('#productos-')) {
		const hashCategory = decodeURIComponent(hash.replace('#productos-', ''));
		if (hashCategory === '__offers__' || getNode(hashCategory)?.count > 0) {
			applyCategory(hashCategory, false);
		} else {
			renderExplorer();
			applyCategory('__offers__', false);
		}
	} else if (hash.startsWith('#categorias-')) {
		const hashPath = decodeURIComponent(hash.replace('#categorias-', ''));
		if (!hashPath || getNode(hashPath)?.count > 0) {
			applyBrowse(hashPath, false);
		} else {
			renderExplorer();
			applyCategory('__offers__', false);
		}
	} else {
		renderExplorer();
		applyCategory('__offers__', false);
	}
}

document.querySelectorAll('[data-cart-form]').forEach((form) => {
	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		const button = form.querySelector('button[type="submit"], button.add-button');
		const originalLabel = button.querySelector('.add-button-label');
		button.disabled = true;

		try {
			const response = await fetch(form.action, {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
				body: new FormData(form),
			});
			if (!response.ok) throw new Error('No se pudo añadir el producto.');
			const payload = await response.json();
			document.querySelectorAll('[data-cart-count]').forEach((counter) => {
				counter.textContent = payload.cart_count;
				counter.classList.add('cart-count-bump');
				window.setTimeout(() => counter.classList.remove('cart-count-bump'), 500);
			});
			const card = form.closest('.product-card');
			const stockLabel = card?.querySelector('.availability-dot');
			if (stockLabel) {
				stockLabel.textContent = payload.stock_remaining > 0
					? `● Disponible · ${payload.stock_remaining} uds.`
					: '● Agotado';
			}
			if (payload.stock_remaining === 0) {
				button.disabled = true;
				if (originalLabel) originalLabel.textContent = 'Agotado';
			} else if (originalLabel) {
				originalLabel.textContent = '¡Añadido!';
			}
			showCartToast(payload.message);
			window.setTimeout(() => { if (originalLabel && payload.stock_remaining > 0) originalLabel.textContent = 'Añadir'; }, 1200);
		} catch (error) {
			showCartToast(error.message, true);
		} finally {
			button.disabled = false;
		}
	});
});

const cartPage = document.querySelector('[data-cart-page]');

if (cartPage) {
	window.addEventListener('pageshow', (event) => {
		if (event.persisted) window.location.reload();
	});

	const formatCartPrice = (value) => `S/ ${Number(value).toFixed(2).replace('.', ',')}`;
	const updateCartCounters = (payload) => {
		document.querySelectorAll('[data-cart-count]').forEach((counter) => {
			counter.textContent = payload.cart_count;
			counter.classList.add('cart-count-bump');
			window.setTimeout(() => counter.classList.remove('cart-count-bump'), 500);
		});
		const totalItems = cartPage.querySelector('[data-cart-total-items]');
		const total = cartPage.querySelector('[data-cart-total]');
		if (totalItems) totalItems.textContent = `${payload.cart_count} unidad(es)`;
		if (total) total.textContent = formatCartPrice(payload.cart_total);
	};
	const showEmptyCart = () => {
		cartPage.innerHTML = `<div class="cart-heading"><div><p class="cart-kicker"><span>✦</span> Tu selección</p><h1>Carrito de compra</h1><p class="cart-subtitle">Lo que elegiste para llevar tu día un poco más lejos.</p></div></div><div class="cart-empty"><div class="cart-empty-icon" aria-hidden="true"><span>✦</span></div><p class="cart-empty-kicker">Tu próxima elección te espera</p><h2>Tu carrito está vacío.</h2><p>Añade algo especial y deja que la tecnología acompañe tu próximo paso.</p><a href="${cartPage.dataset.storeHome}" class="cart-back-button">Explorar la colección <span>↗</span></a></div>`;
	};

	cartPage.querySelectorAll('[data-cart-item-form]').forEach((form) => {
		form.addEventListener('submit', async (event) => {
			event.preventDefault();
			const button = form.querySelector('button');
			const row = form.closest('[data-cart-item]');
			button.disabled = true;

			try {
				const response = await fetch(form.action, {
					method: form.querySelector('[name="_method"]')?.value || 'POST',
					headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
					body: new FormData(form),
				});
				const payload = await response.json();
				if (!response.ok) throw new Error(payload.message || 'No se pudo actualizar el carrito.');

				updateCartCounters(payload);
				if (payload.removed) {
					row.remove();
					if (!cartPage.querySelector('[data-cart-item]')) showEmptyCart();
				} else {
					row.querySelector('[data-cart-quantity]').textContent = payload.quantity;
					const description = row.querySelector('[data-cart-item-description]');
					const unitPrice = description.textContent.match(/S\/ [\d,]+/i)?.[0] || '';
					if (description) description.textContent = `${payload.quantity} unidad(es) · ${unitPrice} c/u`;
					row.querySelector('[data-cart-item-total]').textContent = formatCartPrice(payload.item_total);
				}
				showCartToast(payload.message);
			} catch (error) {
				showCartToast(error.message, true);
			} finally {
				if (button) button.disabled = false;
			}
		});
	});
}

const checkoutForm = document.querySelector('.checkout-layout');

if (checkoutForm) {
	const checkoutSteps = [...checkoutForm.querySelectorAll('[data-checkout-step]')];
	const checkoutProgressSteps = [...document.querySelectorAll('[data-checkout-progress-step]')];
	const checkoutSubmit = checkoutForm.querySelector('[data-checkout-submit]');
	const validatedCheckoutSteps = new Set();
	let currentCheckoutStep = 1;

	document.body.classList.add('checkout-wizard-active');
	checkoutForm.noValidate = true;

	const getCheckoutStep = (stepNumber) => checkoutSteps.find((step) => Number(step.dataset.checkoutStep) === stepNumber);
	const setCheckoutStep = (stepNumber, shouldScroll = true) => {
		currentCheckoutStep = stepNumber;
		document.body.dataset.checkoutCurrentStep = String(stepNumber);
		checkoutSteps.forEach((step) => { step.hidden = Number(step.dataset.checkoutStep) !== stepNumber; });
		checkoutProgressSteps.forEach((progressStep) => {
			const progressStepNumber = Number(progressStep.dataset.checkoutProgressStep);
			const isCurrent = progressStepNumber === stepNumber;
			progressStep.classList.toggle('is-current', isCurrent);
			progressStep.classList.toggle('is-complete', progressStepNumber < stepNumber);
			if (isCurrent) progressStep.setAttribute('aria-current', 'step');
			else progressStep.removeAttribute('aria-current');
		});
		if (checkoutSubmit) checkoutSubmit.hidden = stepNumber !== 3;

		if (shouldScroll && window.matchMedia('(max-width: 900px)').matches) {
			getCheckoutStep(stepNumber)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	};
	const validateCheckoutStep = (stepNumber) => {
		const step = getCheckoutStep(stepNumber);
		const requiredFields = [...(step?.querySelectorAll('[required]') || [])];
		const invalidField = requiredFields.find((field) => !field.disabled && !field.closest('[hidden]') && !field.checkValidity());

		if (invalidField) {
			invalidField.reportValidity();
			return false;
		}

		validatedCheckoutSteps.add(stepNumber);
		return true;
	};

	checkoutForm.querySelectorAll('[data-checkout-next]').forEach((button) => {
		button.addEventListener('click', () => {
			if (validateCheckoutStep(currentCheckoutStep)) setCheckoutStep(Math.min(currentCheckoutStep + 1, 3));
		});
	});
	checkoutForm.querySelectorAll('[data-checkout-back]').forEach((button) => {
		button.addEventListener('click', () => setCheckoutStep(Math.max(currentCheckoutStep - 1, 1)));
	});
	checkoutForm.addEventListener('submit', (event) => {
		if (currentCheckoutStep < 3) {
			event.preventDefault();
			if (validateCheckoutStep(currentCheckoutStep)) setCheckoutStep(currentCheckoutStep + 1);
			return;
		}

		const firstUnvalidatedStep = [1, 2].find((stepNumber) => !validatedCheckoutSteps.has(stepNumber));
		if (firstUnvalidatedStep) {
			event.preventDefault();
			setCheckoutStep(firstUnvalidatedStep);
			return;
		}

		if (!validateCheckoutStep(3)) event.preventDefault();
	});

	const firstServerErrorStep = checkoutForm.querySelector('.checkout-error')?.closest('[data-checkout-step]');
	const firstNonemptyInvalidStep = checkoutSteps.find((step) => [...step.querySelectorAll('input, select, textarea')].some((field) => (
		!field.disabled
		&& !field.closest('[hidden]:not([data-checkout-step])')
		&& !field.checkValidity()
		&& (field.type === 'radio' ? field.checked : String(field.value).trim() !== '' || field.validity.badInput)
	)));
	const initialErrorStep = Number(firstServerErrorStep?.dataset.checkoutStep || firstNonemptyInvalidStep?.dataset.checkoutStep || 1);
	setCheckoutStep(initialErrorStep, false);
}

const ubigeoFields = document.querySelector('[data-ubigeo-fields]');
const fulfillmentFields = document.querySelector('[data-ubigeo-fields]');
const fulfillmentNotice = document.querySelector('[data-fulfillment-notice]');

if (fulfillmentFields && fulfillmentNotice) {
	const deliveryInputs = [...fulfillmentFields.querySelectorAll('select, textarea')];
	const departmentSelect = fulfillmentFields.querySelector('[data-ubigeo-department]');
	const provinceSelect = fulfillmentFields.querySelector('[data-ubigeo-province]');
	const districtSelect = fulfillmentFields.querySelector('[data-ubigeo-district]');
	const addressField = fulfillmentFields.querySelector('[name="customer_address"]');
	const title = fulfillmentNotice.querySelector('[data-fulfillment-title]');
	const copy = fulfillmentNotice.querySelector('[data-fulfillment-copy]');
	const extra = fulfillmentNotice.querySelector('[data-fulfillment-extra]');
	const syncFulfillment = (method) => {
		const isPickup = method === 'pickup';
		fulfillmentFields.hidden = isPickup;
		fulfillmentFields.classList.toggle('is-pickup', isPickup);
		if (departmentSelect) departmentSelect.disabled = isPickup;
		if (provinceSelect && isPickup) provinceSelect.disabled = true;
		if (districtSelect && isPickup) districtSelect.disabled = true;
		if (provinceSelect && !isPickup) provinceSelect.disabled = provinceSelect.options.length <= 1;
		if (districtSelect && !isPickup) districtSelect.disabled = districtSelect.options.length <= 1;
		if (addressField) {
			addressField.required = !isPickup;
			addressField.disabled = isPickup;
		}
		deliveryInputs.filter((field) => ![addressField, provinceSelect, districtSelect, departmentSelect].includes(field)).forEach((field) => { field.disabled = isPickup; });
		title.textContent = isPickup ? 'Recojo en tienda:' : 'Delivery:';
		copy.textContent = isPickup ? 'Disponible en Miguel Iglesias 991, Cajamarca' : 'costo a cargo del cliente';
		extra.textContent = isPickup ? 'Te esperamos para recoger tu pedido.' : '(se coordina aparte)';
		fulfillmentNotice.classList.toggle('is-pickup', isPickup);
	};

	document.querySelectorAll('input[name="fulfillment_method"]').forEach((radio) => {
		radio.addEventListener('change', () => syncFulfillment(radio.value));
	});
	syncFulfillment(document.querySelector('input[name="fulfillment_method"]:checked')?.value || 'delivery');
}

const paymentDetails = document.querySelector('[data-payment-details]');

if (paymentDetails) {
	const paymentFields = [...paymentDetails.querySelectorAll('[data-payment-field]')];
	const yapeInstructions = document.querySelector('[data-yape-instructions]');
	const paymentQrCards = [...document.querySelectorAll('[data-payment-qr-card]')];
	const bankTransferInstructions = document.querySelector('[data-bank-transfer-instructions]');
	const syncPaymentDetails = (method) => {
		const requiresDetails = ['yape', 'plin', 'transferencia'].includes(method);
		paymentDetails.hidden = !requiresDetails;
		if (yapeInstructions) yapeInstructions.hidden = method !== 'yape';
		if (bankTransferInstructions) bankTransferInstructions.hidden = method !== 'transferencia';
		paymentQrCards.forEach((card) => {
			card.hidden = card.dataset.paymentQrCard !== method;
		});
		paymentFields.forEach((field) => {
			field.required = requiresDetails;
			if (!requiresDetails) field.value = '';
		});
	};

	document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
		radio.addEventListener('change', () => syncPaymentDetails(radio.value));
	});
	syncPaymentDetails(document.querySelector('input[name="payment_method"]:checked')?.value || '');
}

if (ubigeoFields) {
	const departmentSelect = ubigeoFields.querySelector('[data-ubigeo-department]');
	const provinceSelect = ubigeoFields.querySelector('[data-ubigeo-province]');
	const districtSelect = ubigeoFields.querySelector('[data-ubigeo-district]');
	const source = ubigeoFields.dataset.ubigeoSource;

	const resetSelect = (select, label) => {
		select.innerHTML = `<option value="">${label}</option>`;
		select.disabled = true;
	};
	const fillSelect = (select, items, label) => {
		select.innerHTML = `<option value="">${label}</option>${items.map((item) => `<option value="${item.name}" data-ubigeo-code="${item.code}">${item.name}</option>`).join('')}`;
		select.disabled = items.length === 0;
	};
	const selectedCode = (select) => select.selectedOptions[0]?.dataset.ubigeoCode || '';
	const selectValue = (select, value) => {
		const option = [...select.options].find((candidate) => candidate.value === value || candidate.dataset.ubigeoCode === value);
		if (option) select.value = option.value;
	};

	fetch(source)
		.then((response) => {
			if (!response.ok) throw new Error('No se pudo cargar las ubicaciones.');
			return response.json();
		})
		.then((locations) => {
			const departments = [...new Map(locations.filter((location) => location.provincia === '00').map((location) => [location.departamento, { code: location.departamento, name: location.nombre }])).values()];
			fillSelect(departmentSelect, departments, 'Selecciona un departamento');
			const oldDepartment = ubigeoFields.dataset.oldDepartment;
			const oldProvince = ubigeoFields.dataset.oldProvince;
			const oldDistrict = ubigeoFields.dataset.oldDistrict;
			if (oldDepartment) {
				selectValue(departmentSelect, oldDepartment);
				departmentSelect.dispatchEvent(new Event('change'));
			}

			departmentSelect.addEventListener('change', () => {
				resetSelect(districtSelect, 'Selecciona un distrito');
				const provinces = [...new Map(locations.filter((location) => location.departamento === selectedCode(departmentSelect) && location.provincia !== '00' && location.distrito === '00').map((location) => [location.provincia, { code: location.provincia, name: location.nombre }])).values()];
				fillSelect(provinceSelect, provinces, 'Selecciona una provincia');
				if (oldProvince) {
					selectValue(provinceSelect, oldProvince);
					provinceSelect.dispatchEvent(new Event('change'));
				}
			});

			provinceSelect.addEventListener('change', () => {
				const districts = locations.filter((location) => location.departamento === selectedCode(departmentSelect) && location.provincia === selectedCode(provinceSelect) && location.distrito !== '00').map((location) => ({ code: location.distrito, name: location.nombre }));
				fillSelect(districtSelect, districts, 'Selecciona un distrito');
				if (oldDistrict) selectValue(districtSelect, oldDistrict);
			});

			if (oldDepartment) {
				selectValue(departmentSelect, oldDepartment);
				departmentSelect.dispatchEvent(new Event('change'));
			}
		})
		.catch(() => {
			departmentSelect.innerHTML = '<option value="">No se pudieron cargar las ubicaciones</option>';
		});
}

function showCartToast(message, isError = false) {
	let toast = document.querySelector('[data-cart-toast]');
	if (!toast) {
		toast = document.createElement('div');
		toast.dataset.cartToast = 'true';
		document.body.appendChild(toast);
	}
	toast.textContent = message;
	toast.className = `cart-toast ${isError ? 'is-error' : ''} is-visible`;
	window.clearTimeout(toast.hideTimer);
	toast.hideTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 2400);
}

const orderTrackingModal = document.querySelector('[data-order-tracking-modal]');
const orderTrackingForm = document.querySelector('[data-order-tracking-form]');

if (orderTrackingModal && orderTrackingForm) {
	const results = orderTrackingModal.querySelector('[data-order-tracking-results]');
	const errorBox = orderTrackingModal.querySelector('[data-order-tracking-error]');
	const submitButton = orderTrackingForm.querySelector('button[type="submit"]');
	const submitLabel = orderTrackingForm.querySelector('[data-order-tracking-submit-label]');
	const spinner = orderTrackingForm.querySelector('[data-order-tracking-spinner]');
	const reset = () => {
		orderTrackingForm.reset();
		errorBox.textContent = '';
		results.replaceChildren();
		submitButton.disabled = false;
		spinner.classList.remove('is-visible');
		submitLabel.textContent = 'Consultar pedidos';
	};
	const close = () => { reset(); orderTrackingModal.classList.remove('is-visible'); orderTrackingModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('modal-open'); };
	const open = () => { reset(); orderTrackingModal.classList.add('is-visible'); orderTrackingModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('modal-open'); window.setTimeout(() => orderTrackingForm.querySelector('input')?.focus(), 100); };
	const price = (value) => `S/ ${Number(value || 0).toFixed(2).replace('.', ',')}`;
	const date = (value) => value ? new Intl.DateTimeFormat('es-PE', { dateStyle: 'medium' }).format(new Date(value)) : 'Fecha no disponible';
	const paymentLabels = { yape: 'Yape', plin: 'Plin', transferencia: 'Transferencia', contra_entrega: 'Contra entrega' };
	const fulfillmentLabels = { delivery: 'Delivery', pickup: 'Recojo en tienda' };
	const render = (orders) => {
		results.replaceChildren();
		orders.forEach((order) => {
			const card = document.createElement('article'); card.className = 'order-tracking-order';
			const heading = document.createElement('div'); heading.className = 'order-tracking-order-heading';
			const title = document.createElement('h3'); title.textContent = order.order_number;
			const status = document.createElement('span'); status.className = `order-status-badge order-status-${order.status}`; status.textContent = order.status_label;
			heading.append(title, status);
			const meta = document.createElement('p'); meta.className = 'order-tracking-meta'; meta.textContent = `${date(order.created_at)} · ${fulfillmentLabels[order.fulfillment_method] || order.fulfillment_method || 'Modalidad no disponible'}`;
			const details = document.createElement('div'); details.className = 'order-tracking-order-details';
			const total = document.createElement('strong'); total.textContent = price(order.total);
			const payment = document.createElement('span'); payment.textContent = paymentLabels[order.payment_method] || order.payment_method || 'Pago no disponible'; details.append(total, payment);
			const items = document.createElement('ul'); items.className = 'order-tracking-items';
			(order.items || []).forEach((item) => { const line = document.createElement('li'); line.textContent = `${item.name} · ${item.quantity} × ${price(item.price)}`; items.appendChild(line); });
			card.append(heading, meta, details, items); results.appendChild(card);
		});
	};
	document.querySelectorAll('[data-open-order-tracking]').forEach((button) => button.addEventListener('click', open));
	orderTrackingModal.querySelector('[data-close-order-tracking]')?.addEventListener('click', close);
	orderTrackingModal.addEventListener('click', (event) => { if (event.target === orderTrackingModal) close(); });
	document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && orderTrackingModal.classList.contains('is-visible')) close(); });
	orderTrackingForm.addEventListener('submit', async (event) => {
		event.preventDefault(); errorBox.textContent = ''; results.replaceChildren(); submitButton.disabled = true; spinner.classList.add('is-visible'); submitLabel.textContent = 'Buscando...';
		try {
			const response = await fetch(orderTrackingForm.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }, body: new FormData(orderTrackingForm) });
			const payload = await response.json();
			if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'No se pudo consultar el pedido.');
			render(payload.orders || []);
		} catch (trackingError) { errorBox.textContent = trackingError.message; } finally { submitButton.disabled = false; spinner.classList.remove('is-visible'); submitLabel.textContent = 'Consultar pedidos'; }
	});
}
