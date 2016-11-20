# monzofony
Monzo Dashboard built with the Symfony Framework

I started this project to play with the Monzo API a bit more and to get a better understanding of Symfony. It is not complete and the code is not in the best shape as I'm constantly learning new stuff and changing it often.

I have used the following frameworks:
- [Symfony 3.1](https://symfony.com)
- [Foundation Framework](http://foundation.zurb.com) (included in the assets folder, but ended up not using it)
- [MaterializeCSS](http://materializecss.com) (along with [Font Awesome](http://fontawesome.io) for icons)

To handle the oauth and access the data from the Monzo API I have used the following two packages:
- [edcs/mondo-php](https://github.com/edcs/mondo-php)
- [edcs/oauth2-mondo](https://github.com/edcs/oauth2-mondo)

At the moment there is only a homepage with a list of all your transactions grouped by date, similar to what Monzo do on the app.

I've got plans to include a page to view all(or subset of) transactions on a map, probably using [LeafletJS](http://leafletjs.com); and also other bits of functionality such as exporting reports, viewing individual transactions in more detail (so it includes attachments for example), etc.

If anyone wants to set it up, simply
```
fork it
clone it
run `composer install`
include these parameters in the parameters.yml file:
  client_id: CLIENT_ID
  client_secret: CLIENT_SECRET
  redirect_url: MY_URL/login
  mapbox_username: MAPBOX_USERNAME
  mapbox_project_id: MAPBOX_PROJECT_ID
  mapbox_access_token: MAPBOX_ACCESS_TOKEN

```
and it **should** work

I'm using LeafletJS because I always wanted to give it a go and never got around to it. So I'm trying it now. Feel free to change that to something else.
To use get the mapbox parameters to go [Mapbox](https://www.mapbox.com/) and sign up for an account (it's free).

#### TODO
- Tidy up collapsed transaction detail on the list of transactions page
- Map page
- Provide way to limit amount of transactions by date ranges (last week, last month, last 3 months, all?)
- Remove any inline css styles I applied in haste :(

#### IN PROGRESS
- Individual transaction page

#### DONE
- Basic list of transactions
