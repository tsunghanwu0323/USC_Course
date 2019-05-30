import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppComponent } from './app.component';
import { SearchFormComponent } from './search-form/search-form.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CapToVarPipe } from './pipes/cap-to-var.pipe';
import { SearchService } from './services/search.service';
import { DetailsService } from './services/details.service';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { ResultContainerComponent } from './result-container/result-container.component';
import { DetailsComponent } from './details/details.component';
import { InfoTabComponent } from './details/info-tab/info-tab.component';
import { MapTabComponent } from './details/map-tab/map-tab.component';
import { LoaderComponent } from './loader/loader.component';
import { ReviewsTabComponent } from './details/reviews-tab/reviews-tab.component';
import { PhotosTabComponent } from './details/photos-tab/photos-tab.component';
import { AutocompleteDirective } from './directives/autocomplete.directive';
import { WhitespaceDirective } from './directives/whitespace.directive';
import { FavoriteComponent } from './favorite/favorite.component';
import { ResultTableComponent } from './result-table/result-table.component';
import { StarComponent } from './star/star.component';
import { DateTimePipe } from './pipes/date-time.pipe';
import { LoaderService } from './loader/loader.service';
import { FavoriteService } from './services/favorite.service';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { LoaderInterceptor } from './loader/loader.interceptor';

@NgModule({
  declarations: [
    AppComponent,
    SearchFormComponent,
    CapToVarPipe,
    ResultContainerComponent,
    DetailsComponent,
    InfoTabComponent,
    MapTabComponent,
    LoaderComponent,
    ReviewsTabComponent,
    PhotosTabComponent,
    AutocompleteDirective,
    WhitespaceDirective,
    FavoriteComponent,
    ResultTableComponent,
    StarComponent,
    DateTimePipe
  ],
  imports: [
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule,
    BrowserAnimationsModule
  ],
  providers: [
    SearchService,
    DetailsService,
    LoaderService,
    FavoriteService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
