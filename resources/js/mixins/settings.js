export const settings = {
    data() {
        return {
            domainapi: "",
            settings: {},
            langs: [],
            lang: '',
            langsubs: [],
            langsub: '',
            langdownload: '',
            substitles_langs: [],
        };
    },
    async mounted() {
        try {
            let response = await axios.get(url + '/admin/settings/data');
            this.settings = response.data;
        } catch (e) {
            console.error('Failed to load settings:', e);
        }

        try {
            let langResponse = await axios.get(url + '/admin/languages/tmdb');
            this.langs = langResponse.data || [];
            this.langsubs = langResponse.data || [];
        } catch (e) {
            console.error('Failed to load languages from backend:', e);
            this.langs = [
                { iso_639_1: 'pt-br', english_name: 'Portuguese (Brazil)', name: 'Português (Brasil)' },
                { iso_639_1: 'pt', english_name: 'Portuguese', name: 'Português' },
                { iso_639_1: 'en', english_name: 'English', name: 'English' },
                { iso_639_1: 'es-MX', english_name: 'Español Latino', name: 'Español Latino' },
                { iso_639_1: 'es', english_name: 'Spanish', name: 'Español' },
                { iso_639_1: 'fr', english_name: 'French', name: 'Français' },
                { iso_639_1: 'de', english_name: 'German', name: 'Deutsch' },
                { iso_639_1: 'it', english_name: 'Italian', name: 'Italiano' }
            ];
            this.langsubs = this.langs;
        }
    },
};
