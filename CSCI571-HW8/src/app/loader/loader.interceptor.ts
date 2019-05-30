import { Injectable } from '@angular/core';
import {HttpEvent, HttpEventType, HttpInterceptor, HttpHandler, HttpRequest} from '@angular/common/http';
import { Observable } from 'rxjs';
import { LoaderService } from './loader.service';
import { finalize } from 'rxjs/operators';

@Injectable()
export class LoaderInterceptor implements HttpInterceptor {
  constructor(private loaderService: LoaderService) {}
  intercept(
    req: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    const handler = next.handle(req);
    let started = false;
    const resSubscribe = handler.subscribe.bind(handler);
    handler.subscribe = (...args) => {
      this.loaderService.show();
      started = true;
      return resSubscribe(...args);
    };
    return finalize.call(handler, () => started && this.loaderService.hide());
  }
}
