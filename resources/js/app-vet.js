import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;




// Hacerlo accesible globalmente
window.FullCalendar = {
  Calendar,
  dayGridPlugin,
  timeGridPlugin,
  listPlugin,
  interactionPlugin
};

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
