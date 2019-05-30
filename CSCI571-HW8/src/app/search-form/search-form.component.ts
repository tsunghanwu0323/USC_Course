import { Component, OnInit, Output, EventEmitter, ChangeDetectorRef } from '@angular/core';
import { SearchForm } from './search-form';
import { NgForm } from '@angular/forms/src/directives/ng_form';
import { SearchService } from '../services/search.service';


@Component({
  selector: 'app-search-form',
  templateUrl: './search-form.component.html',
  styleUrls: ['./search-form.component.css']
})

export class SearchFormComponent implements OnInit {

  constructor(private sService: SearchService, private cdRef: ChangeDetectorRef) {}
  userLocation: object;
  gotgeojson = false;
  category = 'default';
  form = SearchForm;

  searchTypes = ['Default',
    'Airport',
    'Amusement Park',
    'Aquarium',
    'Art Gallery',
    'Bakery', 'Bar',
    'Beauty Salon',
    'Bowling Alley',
    'Bus Station',
    'Cafe',
    'Campground',
    'Car Rental',
    'Casino',
    'Lodging',
    'Movie Theater',
    'Museum',
    'Night Club',
    'Park',
    'Parking',
    'Restaurant',
    'Shopping Mall',
    'Stadium',
    'Subway Station',
    'Taxi Stand',
    'Train Station',
    'Transit Station',
    'Travel Agency',
    'Zoo'
  ];

  clear() {
    this.sService.clear();
    this.category = 'default';
    this.form.isUserInput = false;
  }

  getAddressOnChange(event, location) {
    this.form.location = (document.getElementById('loc-input') as HTMLInputElement).value;
  }

  getGeo() {
    this.sService.getGeolocation().subscribe(data => {
      this.userLocation = {
        lat: data['lat'],
        lng: data['lon']
      };
      this.form.geoJSON = this.userLocation;
      this.gotgeojson = true;
    });
    console.log(this.form);
  }

  onSubmit() {
    this.sService.search(this.form);
  }

  ngOnInit() {
    this.getGeo();
  }
}

