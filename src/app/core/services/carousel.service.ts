import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';

import { Carousel } from '../models/carousel.model';

@Injectable({
  providedIn: 'root'
})
export class CarouselService implements OnDestroy {

  // Public fields
  public _items$ = new BehaviorSubject<Carousel[]>([]);
  // Private fields
  private _subscriptions: Subscription[] = [];

  // Getters
  get items$() {
    return this._items$.asObservable();
  }
  get subscriptions() {
    return this._subscriptions;
  }  

  protected http: HttpClient;
  // API URL has to be overrided
  API_URL = `${environment.apiUrl}/clients`;
  constructor(http: HttpClient) {
    this.http = http;
  }

  // READ
  getCarousel(): Observable<Carousel[]> { 
    return this.http.get<Carousel[]>(`${this.API_URL}/carousel`).pipe(
      map((response: Carousel[]) => {
        const result = response;
        if(result) {
          this._items$.next(result);
        }
        return result;
      }),
    );
  }  

  getPostCategoriesById(post_id?: number): Observable<Carousel[]> { 
    return this.http.get<Carousel[]>(`${this.API_URL}/Carousel/${post_id}`).pipe(
      map((response: Carousel[]) => {
        const result = response;
        if(result) {
          this._items$.next(result);
        }
        return result;
      }),
    );
  }    
     
  ngOnDestroy() {
    this.subscriptions.forEach(sb => sb.unsubscribe());
  }  
}
