// // Esta función asume que existe un elemento DOM, por ejemplo, <div id="floating-cart-container"></div>
// async function updateCartFloatingComponent() {
//     const cartContainer = document.getElementById('floating-cart-container'); // Reemplaza con el ID/clase de tu contenedor real
//     if (!cartContainer) return;

//     try {
//         const response = await fetch('/cart/contents', { // Llama a la ruta que acabamos de crear
//             headers: {
//                 'X-Requested-With': 'XMLHttpRequest' // Cabecera para que Laravel detecte que es una petición AJAX
//             }
//         });

//         if (!response.ok) {
//             throw new Error(`Error HTTP! estado: ${response.status}`);
//         }

//         const data = await response.json();

//         // Ejemplo de cómo renderizar el contenido del carrito
//         let cartHtml = '<h3 class="text-xl font-bold mb-4 text-[var(--bluey-dark)]">Tu Carrito</h3>';
//         if (data.total_items > 0) {
//             cartHtml += '<ul class="space-y-3">';
//             for (const productId in data.cart) {
//                 const item = data.cart[productId];
//                 // Genera la URL de la imagen del producto
//                 const imageUrl = `/storage/${item.image}`; // Ajusta si tu ruta de Storage es diferente

//                 cartHtml += `
//                     <li class="flex items-center gap-3 border-b pb-3 border-gray-200 last:border-b-0 last:pb-0">
//                         <img src="${imageUrl}" alt="${item.name}" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
//                         <div class="flex-grow">
//                             <p class="font-semibold text-[var(--black)]">${item.name}</p>
//                             <p class="text-sm text-[var(--dark-gray)]">Cantidad: ${item.quantity}</p>
//                             <p class="text-md font-bold text-[var(--bluey-dark)]">$${(item.final_price_per_unit * item.quantity).toFixed(2)}</p>
//                             ${item.applied_promotion_titles && item.applied_promotion_titles.length > 0 ?
//                                 item.applied_promotion_titles.map(promoTitle => `<p class="text-xs text-[var(--yellow-dark)]">🎁 ${promoTitle}</p>`).join('') : ''}
//                             ${item.gift_quantity > 0 ? `<p class="text-xs text-[var(--green)]">¡+${item.gift_quantity} de regalo!</p>` : ''}
//                         </div>
//                         <button class="remove-from-cart-btn text-red-500 hover:text-red-700 font-bold text-lg" data-id="${item.id}">✖</button>
//                     </li>
//                 `;
//             }
//             cartHtml += `</ul>
//                          <div class="mt-4 pt-4 border-t border-gray-200">
//                             <p class="text-lg font-bold flex justify-between"><span>Subtotal:</span> <span>$${data.subtotal}</span></p>
//                             <a href="/checkout" class="mt-4 block w-full text-center px-4 py-2 bg-[var(--bluey-primary)] hover:bg-[var(--bluey-secondary)] text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
//                                 Ir a Pagar
//                             </a>
//                         </div>`;
//         } else {
//             cartHtml += '<p class="text-center text-gray-500 py-4">El carrito está vacío.</p>';
//         }

//         cartContainer.innerHTML = cartHtml;

//         // Actualiza el contador del carrito (la insignia)
//         const cartBadge = document.querySelector('.cart-badge');
//         if (cartBadge) {
//             cartBadge.textContent = data.total_items;
//             // Haz el badge visible si hay items, invisible si no
//             if (data.total_items > 0) {
//                 cartBadge.classList.remove('hidden');
//             } else {
//                 cartBadge.classList.add('hidden');
//             }
//         }

//         // Añade oyentes de eventos para los botones de eliminar (si los añades al carrito flotante)
//         cartContainer.querySelectorAll('.remove-from-cart-btn').forEach(button => {
//             button.addEventListener('click', async (event) => {
//                 const productId = event.target.dataset.id;
//                 await removeFromCart(productId); // Llama a la función para eliminar del carrito
//             });
//         });

//     } catch (error) {
//         console.error('Error al obtener el contenido del carrito:', error);
//         if (cartContainer) {
//             cartContainer.innerHTML = '<p class="text-center text-red-500 py-4">Error al cargar el carrito.</p>';
//         }
//     }
// }

// // Función para manejar la eliminación del carrito
// async function removeFromCart(productId) {
//     try {
//         const res = await fetch(`/cart/remove/${productId}`, {
//             method: 'DELETE',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': '{{ csrf_token() }}' // Asegúrate de que el token CSRF esté disponible
//             }
//         });
//         const data = await res.json();

//         if (res.ok && data.success) {
//             showCartAlert(data.message, true);
//             await updateCartFloatingComponent(); // Refresca el carrito flotante
//         } else {
//             showCartAlert(data.message, false);
//         }
//     } catch (err) {
//         console.error('Error al eliminar del carrito:', err);
//         showCartAlert('Hubo un error de conexión al eliminar el producto.', false);
//     }
// }

// // Llama inicialmente a la función para cargar el carrito cuando la página se carga
// document.addEventListener('DOMContentLoaded', () => {
//     // Es buena práctica que este componente se cargue al inicio
//     updateCartFloatingComponent();
// });