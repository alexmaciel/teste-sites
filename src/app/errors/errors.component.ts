import { Component, HostBinding, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';

@Component({
  selector: 'app-errors',
  templateUrl: './errors.component.html',
})
export class ErrorsComponent implements OnInit {
  @HostBinding('class') class = 'd-flex flex-column flex-root';
  
  constructor(
    private router: Router,
    private localize: LocalizeRouterService
  ) {}

  ngOnInit(): void {}

  routeToPage(path: string) {
    const translatedPath = this.localize.translateRoute(path);

    this.router.navigate([translatedPath]).then(() => {
      // console.log(`After navigation I am on: ${translatedPath}`)
     }); 
  }
}
