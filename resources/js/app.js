import './bootstrap';
import Alpine from 'alpinejs';
import Persist from '@alpinejs/persist';
import Collapse from '@alpinejs/collapse';

Alpine.plugin(Persist);
Alpine.plugin(Collapse);

window.Alpine = Alpine;
Alpine.start();
