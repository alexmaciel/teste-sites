import { Injectable, OnDestroy } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';
import { finalize, map } from 'rxjs/operators';

import { environment } from '../../../environments/environment'

import { Videos } from '../models';

@Injectable({
  providedIn: 'root'
})
export class TechnologyVideoService implements OnDestroy {

  // Public fields
  public _items$ = new BehaviorSubject<Videos[]>([]);
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
  getTechnologyVideos(): Observable<Videos[]> { 
    return this.http.get<Videos[]>(`${this.API_URL}/technologyVideos/`).pipe(
      map((response: Videos[]) => {
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
