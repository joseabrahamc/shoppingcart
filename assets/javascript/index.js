import { HttpRequests } from './httpRequests'
import { UI } from './ui'

const http = new HttpRequests()
const ui = new UI()
const baseURL = 'http://localhost/shoppingcart'

ui.baseURL = baseURL
ui.http = http

// Load event listeners
document.addEventListener('DOMContentLoaded', function() {
	ui.modalEvents()
	ui.cartEvents()
})

// Toggle element
document.querySelector('body').addEventListener('click', function(e) {
	let element = e.target.closest('[class*="-toggle"]')

	if (element) {
		ui.overlay()
		ui.toggleActive(element)
	}
})

// Load content in toggled element .container
document.querySelector('body').addEventListener('click', async function(e) {
	let element = e.target.closest('.load-template')

	if (element) {
		// Get template name and load it in the container
		let route = element.classList[Array.from(element.classList).indexOf('load-template') + 1]
		let parent = element.getAttribute("id")
		let container = document.querySelector(`.${parent} > .container`)

		ui.emptyContainer(container)
		ui.loadTemplate(await http.get(`${baseURL}/${route}`), container)

		// Specific event listeners for dynamic elements (hardcoded)
		if (route === 'cart') {
			// Set total amount
			ui.handleShipping(document.getElementById('shipping'))
			document.getElementById('shipping').addEventListener('change', ui.handleShipping)
		}
		
		if (parent === 'modal') {
			// Handle stars color
			const rating = parseFloat(document.querySelector('.product-rating > p > span.rating-value').textContent)

			ui.calculateStarsBackground(rating)

			document.querySelector('.product-rating form').addEventListener('mouseout', () => {
				ui.calculateStarsBackground(rating)				
			})
		}
	}
})