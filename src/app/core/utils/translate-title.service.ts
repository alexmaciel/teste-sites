import { Injectable } from '@angular/core';
import { TitleStrategy, RouterStateSnapshot } from '@angular/router';

import { TranslateService } from '@ngx-translate/core';
import { SeoService } from './seo.service';


@Injectable({
  providedIn: 'root'
})
export class TranslateTitleStrategy extends TitleStrategy {
  
  constructor(
    // Services
    private readonly seo: SeoService,    
    private readonly translate: TranslateService,
  ) {
    super();
  }

  override updateTitle(snapshot: RouterStateSnapshot,): void {
    let title = this.buildTitle(snapshot);
    if (!title) {
      title = 'DEFAULT_TITLE';
    }
    this.translate.get(title).subscribe((translatedTitle) => {
      this.seo.setPageTitle(translatedTitle);    
    });
  }
  
}
