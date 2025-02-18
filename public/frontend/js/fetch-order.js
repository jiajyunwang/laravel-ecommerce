document.addEventListener('DOMContentLoaded', function () {
    let page = 1;
    let sortBy = 'created_at';
    let sortOrder = 'desc';
    const orderContainer = document.getElementById('order-container');
    const loading = document.getElementById('loading');
    const orderType = document.getElementById('order-container').dataset.type;

    let observer = new IntersectionObserver(loadMoreOrders); 

    function loadMoreOrders(entries) {
        if (entries[0].isIntersecting) {
            loading.textContent = '';
            loading.classList.add("loader");
            page++;
            fetchOrders(page, sortBy, sortOrder, orderType);
        }
    }

    observer.observe(loading);

    async function fetchOrders(page, sortBy, sortOrder, orderType) {
        try {
            const response = await fetch(
                `/user/orders/fetch?page=${page}&type=${orderType}`
            );
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            loading.classList.remove("loader");
            
            if (html.trim().length === 0) {
                observer.disconnect();
                loading.textContent = '無更多訂單';
                return;
            }
            
            const container = document.getElementById('order-container');
            container.insertAdjacentHTML('beforeend', html);
            
            if (isElementInViewport(loading)) {
                loadMoreOrders([{ isIntersecting: true }]); 
            }
        } catch (error) {
            console.error('Error fetching orders:', error);
        }
    }
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
});