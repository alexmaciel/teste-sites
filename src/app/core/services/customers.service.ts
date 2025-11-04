import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../../environments/environment';
import { Customers } from '../models';

@Injectable({
  providedIn: 'root'
})
export class CustomerService implements OnDestroy  {

  // Public fields
  public _items$ = new BehaviorSubject<Customers[]>([]);
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


  getCustomers(): Observable<Customers[]> { 
    return this.http.get<Customers[]>(`${this.API_URL}/customers`).pipe(
      map((response: Customers[]) => {
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
