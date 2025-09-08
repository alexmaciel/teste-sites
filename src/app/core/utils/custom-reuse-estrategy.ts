import {ActivatedRouteSnapshot, BaseRouteReuseStrategy} from '@angular/router';

export class CustomReuseStrategy extends BaseRouteReuseStrategy {

  override shouldReuseRoute(future: ActivatedRouteSnapshot, curr: ActivatedRouteSnapshot): boolean {
    return future.routeConfig === curr.routeConfig && !!future.data['reuseComponent'];
  }
}