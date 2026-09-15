import settingsComponent from './components/SettingsComponent.vue';
import moviesComponent from './components/MoviesComponent.vue';


export const routes = [
    { path: '/settings', component: settingsComponent, name: 'SettingsComponent',
    path: '/movies', component: moviesComponent, name: 'MoviesComponent'  },
];