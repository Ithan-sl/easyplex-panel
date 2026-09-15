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
        let response = await axios.get(url + '/admin/settings/data');
        this.settings = response.data;


        if(this.settings.internallangs != 1){

        response = await http.get('https://api.themoviedb.org/3/configuration/languages?api_key=' + this.settings.tmdb_api_key);
        this.langs.push({iso_639_1: 'pt-br', english_name: 'Portuguese (Brazil)' , iso_639_1: 'pt-br'});
        this.langs.push({iso_639_1: 'es-MX', english_name: 'Español Latino' , iso_639_1: 'es-MX'});
        
        }else {

           response = await http.get(url + '/admin/languages/data');
        }

      
        this.langs = response.data;

  

        if(this.settings.internallangs != 1){

        response = await http.get('https://api.themoviedb.org/3/configuration/languages?api_key=' + this.settings.tmdb_api_key);
        this.langsubs = response.data;
        this.langsubs.push({iso_639_1: 'pt-br', english_name: 'Portuguese (Brazil)' , iso_639_1: 'pt-br'});
        this.langsubs.push({iso_639_1: 'es-MX', english_name: 'Español Latino' , iso_639_1: 'es-MX'});


        }else {

          response = await http.get(url + '/admin/languages/data');
        }
        this.langsubs = response.data;
  
    },
};
