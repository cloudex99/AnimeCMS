// global
function HelperFunctions() {

    this.slugify = function(text)
    {
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    };

    this.anime_url = function(id,text){
        return '/anime/'+id+'-'+this.slugify(text);
    }

}

// create new object
var cmsfuncs = new HelperFunctions();
