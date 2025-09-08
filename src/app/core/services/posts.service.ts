import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { TranslationService } from './translation.service';

import { environment } from '../../../environments/environment';
import { APIResponseModel, IAPIState, baseFilter } from '../helpers';

import { ApiService } from './api.service';
import { Posts } from '../models';

@Injectable({
  providedIn: 'root'
})
export class PostService extends ApiService<Posts> {

  override API_URL = `${environment.apiUrl}/clients`;

  constructor(@Inject(HttpClient) http: HttpClient, private translation: TranslationService) {
    super(http);
  }
  
  // READ
  override find(apiState: IAPIState | any): Observable<APIResponseModel<Posts>> {
    let params: any = [];

    const filtrationFields = Object.keys(apiState);
    filtrationFields.forEach((keyName) => {
      params[keyName] = apiState[keyName];
    });
    
    return this.http.get<Posts[]>(`${this.API_URL}/posts`, { params }).pipe(
      map((response: Posts[]) => {
        const filteredResult = baseFilter(response.filter(el => el.language === this.translation.currentLanguageValue), apiState);
        const result: APIResponseModel<Posts> = {
          items: filteredResult.items,
          total: filteredResult.total
        };
        return result;
      })
    );
  }  

  override getItemById(slug: string): Observable<Posts> {
    const url = `${this.API_URL}/getPostsBySlug/${slug}`;
    return this.http.get<Posts[]>(url).pipe(
      map((response: Posts[]) => {
        return response.filter(el => el.language === this.translation.currentLanguageValue)[0];
      }),
    );
  }      
  
}
