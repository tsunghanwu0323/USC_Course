const express = require('express');
const request = require('request');
const cors = require('cors')

const yelp = require('yelp-fusion');
const client = yelp.client('Oc39-pu25qwtb4jRpryo3cOCUgTRT0PDKNIZf8Byg3GXv8laI1-mx3tqv83F-J4cLNubEtpoAmAfYEOnr6mkMfdl4EPYAB5Zn_FJRDC7uJS5QNXT8az-mm2YXnXNWnYx');

const app = express();

const APIKey = 'AIzaSyCWxzlQfNtzY-XKn0X3ueJ46OjaR2K4gqM';

app.use((req, res, next) => {
  cors();
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Credentials', 'true');
  next();
})

app.get('/api/nextPage', (req, res, next) => {
  let pageToken = req.query.pagetoken;
  let url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?pagetoken=' + pageToken + '&key=' + APIKey;
  request(url, {json: true}, (err, response, body) => {
    if (err) { return console.log(err); }
    res.send(body);
  })
});

app.get('/api/search', (req, res, next) => {
  let keyword = req.query.keyword;
  let type = req.query.category;
  let radius = req.query.distance * 1600;
  let isUserInput = req.query.isUserInput;
  location = '';
  // User's current location
  if (isUserInput == 'false') {
    location = req.query.geoJson;
    let url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=' + location + '&radius=' + radius + '&type=' + type + '&keyword=' + keyword + '&key=' + APIKey;
    // Get search result
    request(url, {json: true}, (err, response, body) => {
      if (err) { return console.log(err); }
      res.send(body);
  })
  } else {
    let address = req.query.location;
    address = address.split(' ').join('+');
    let url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' + address + '&key=' + APIKey;
    request(url, {json: true}, (err, response, body) => {
      if (err) { return console.log(err); }
      location = body.results[0].geometry.location.lat + ',' + body.results[0].geometry.location.lng;
      let url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=' + location + '&radius=' + radius + '&type=' + type + '&keyword=' + keyword + '&key=' + APIKey;
      // Get search result
      request(url, {json: true}, (err, response, body) => {
        if (err) { return console.log(err); }
        res.send(body);
      })
    })
  }

});

app.get('/api/yelpReview', (req, res, next) => {
  let name= req.query.name;
  let address = req.query.address;
  let city = req.query.city;
  let state = req.query.state;
  let country = req.query.country;

  client.businessMatch({
    name: name,
    address1: address,
    address2: city + ', ' + state,
    city: city,
    state: state,
    country: country
  }).then(response => {
    const id = response.jsonBody.businesses[0].id;

    client.reviews(id).then(response => {
      res.send(response.jsonBody.reviews);
    }).catch(e => {
      console.log(e);
    });
  }).catch(e => {
    console.log(e);
  });

});

module.exports = app;
