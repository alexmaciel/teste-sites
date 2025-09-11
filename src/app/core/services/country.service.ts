import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';
import { Countries } from '../models';

@Injectable({
  providedIn: 'root'
})
export class CountryService implements OnDestroy  {

  // Public fields
  public _items$ = new BehaviorSubject<Countries[]>([]);
  public _subscriptions: Subscription[] = [];

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


  getCountries(): Observable<Countries[]> { 
    return this.http.get<Countries[]>(`${this.API_URL}/countries`).pipe(
      map((response: Countries[]) => {
        if(response) {
          this._items$.next(response);
        }
        return response;
      }),
    );
  } 

  ngOnDestroy() {
    this.subscriptions.forEach(sb => sb.unsubscribe());
  }  
}
